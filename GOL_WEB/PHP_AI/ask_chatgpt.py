import time
from openai import OpenAI
from pathlib import Path
import os


API_KEY = os.environ.get('OPENAI_API_KEY')


class ChatGPTSession:
    def __init__(self, client, assistant_id, previous_rounds=[]):
        self.client = client
        self.assistant_id = assistant_id
        if previous_rounds:
            self.thread = client.beta.threads.create(
                messages=previous_rounds)
            #     [
            #         {
            #             "role" : "user",
            #             "content" : "Сгенерируй новый раунд. Моему герою сейчас 18 лет. Он начал задумываться о первом заработке",
            #             "attachments" : [
            #                 {
            #                     "file_id" : "file-IwozwGNhnRkCa2foJuChW78D",
            #                     "tools" : [{"type" : "file_search"}]
            #                 }
            #             ]
            #         }
            #     ]
            # )
            
        else:
            self.thread = client.beta.threads.create()
            

    def create_msg(self, user_msg):
        print(f"Твоё сообщение: {user_msg}")
        message_create = self.client.beta.threads.messages.create(
            role="user",
            thread_id=self.thread.id,
            content=f"{user_msg}"
        )
        print(f"Что создалось: {message_create}")

    def thread_running(self):
        run = self.client.beta.threads.runs.create(
            thread_id=self.thread.id,
            assistant_id=self.assistant_id
        )
        return run.id

    def refresh_thread(self, run_id):
        run_info = self.client.beta.threads.runs.retrieve(
            thread_id=self.thread.id,
            run_id=run_id
        )

    def get_last_msg(self):
        messages = self.client.beta.threads.messages.list(
            thread_id=self.thread.id
        )
        return messages

    def ask_assistant(self, user_msg):
        self.create_msg(user_msg)
        run_id = self.thread_running()
        time.sleep(15)

        self.refresh_thread(run_id)

        messages = self.get_last_msg()
        answer = messages.data[0].content[0].text.value
        print(answer)


if __name__ == "__main__":

    client = OpenAI(api_key=API_KEY)
    assistant_id = os.environ.get("ASSIST_ID")
    
    session = ChatGPTSession(client, assistant_id)

    while True:
        print("Введите запрос:")
        user_msg = input()
        session.ask_assistant(user_msg)
