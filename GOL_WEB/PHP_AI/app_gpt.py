from flask import Flask, request, jsonify, session as flask_session
import time
from ask_chatgpt import ChatGPTSession
from api_keys import API_KEY, ASSIST_ID
from openai import OpenAI

app = Flask(__name__)
app.secret_key = '180320044002'


client = OpenAI(api_key=API_KEY)
assistant_id = ASSIST_ID

def get_chatgpt_session(data):
    if data['user_id'] in flask_session:
        thread_id = flask_session[data['user_id']]['chatgpt_thread']
        chatgpt_session = ChatGPTSession(client=client, assistant_id=assistant_id)
        chatgpt_session.thread_id = thread_id
    else:
        try:
            chatgpt_session = ChatGPTSession(client=client, assistant_id=assistant_id)
            flask_session[data['user_id']] = {'chatgpt_thread': chatgpt_session.thread_id}
        except Exception as e:
            return False
        
    return chatgpt_session


@app.route('/run_chatgpt', methods=['POST'])
def run_chatgpt():
    app.logger.debug("Received POST request on /run_chatgpt")
    data = request.json
    app.logger.debug(f"Request data: {data}")
    print(data)
    session = get_chatgpt_session(data)
    if not session:
        return jsonify({'error': "Не смогли создать сессию. Попробуй reload"}), 500
    
    answer = None
    max_retries = 3
    if data['first']:
        
        prompt = f"Вот история предыдущих раундов игры и портрет игрока: '{data['story']}'. Герою теперь {data['age']} лет. Текущий счёт: {data['score']}. \
            Сгенерируй раунд, который будет связан с его жизнью и какими-либо обстоятельствами, \
            которые потребуют от него решения задачи по микроэкономике, макроэкономике или финансовым\
            рынкам (при генерации заданий используй файлы)."
        
    else:
        
        prompt = f"Герою теперь {data['age']} лет. Текущий счёт: {data['score']}. Сгенерируй раунд, который будет связан с его \
            жизнью и какими-либо обстоятельствами, которые потребуют от него решения задачи по микроэкономике, \
            макроэкономике или финансовым рынкам (при генерации заданий используй файлы). \
            Необходимо, чтобы раунд соотносился с историей того, что уже происходило в игре."
            
    for attempt in range(max_retries):
        try:
            answer = session.ask_assistant(prompt)
            if answer:  # Убедимся, что ответ не пустой
                break
        except IndexError:
            if attempt < max_retries - 1:
                print(f"Attempt {attempt + 1} failed, retrying...")
                time.sleep(1)  # Задержка перед повторной попыткой
            else:
                return jsonify({'error': 'Failed to get a valid response from the API after several attempts.'}), 500
        except Exception as e:
            return jsonify({'error': str(e)}), 500

    print(answer)
    return jsonify({'question': answer, 'type': 'gpt', 'roundId': 'gpt'})


@app.route('/process_answer', methods=['POST'])
def process_answer():
    data = request.json
    session = get_chatgpt_session(data)
    answer = None
    max_retries = 3
    for attempt in range(max_retries):
        try:
            answer = session.ask_assistant(
                f"Вот мой ответ на предыдущее задание: '{data['ask']}'. Оцени его."
            )
            if answer:  # Убедимся, что ответ не пустой
                break
        except IndexError:
            if attempt < max_retries - 1:
                print(f"Attempt {attempt + 1} failed, retrying...")
                time.sleep(1)  # Задержка перед повторной попыткой
            else:
                return jsonify({'error': 'Failed to get a valid response from the API after several attempts.'}), 500
        except Exception as e:
            return jsonify({'error': str(e)}), 500

    print(answer)
    return jsonify({'message': answer, 'type': 'gpt_answer', 'roundId': 'gpt'})


@app.route('/make_summary', methods=['POST'])
def make_summary():
    data = request.json
    session = get_chatgpt_session(data)
    answer = None
    max_retries = 3
    for attempt in range(max_retries):
        try:
            answer = session.ask_assistant(
                f"Игра закончилась. Составь для игрока небольшое summary о проведенной игре. Текущий возраст героя: {data['age']}. Текущий счёт: {data['balance']}"
            )
            if answer:  # Убедимся, что ответ не пустой
                break
        except IndexError:
            if attempt < max_retries - 1:
                print(f"Attempt {attempt + 1} failed, retrying...")
                time.sleep(1)  # Задержка перед повторной попыткой
            else:
                return jsonify({'error': 'Failed to get a valid response from the API after several attempts.'}), 500
        except Exception as e:
            return jsonify({'error': str(e)}), 500

    print(answer)
    return jsonify({'message': answer, 'type': 'gpt_answer', 'roundId': 'gpt'})


if __name__ == '__main__':
    app.run(host='localhost', port=8000)
