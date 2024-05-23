from openai import OpenAI
from pathlib import Path
import os
import time

API_KEY = os.getenv('OPENAI_API_KEY')
client = OpenAI(api_key=API_KEY)

# Загружаем файл

# file = client.files.create(
#   file=Path('/Applications/MAMP/htdocs/GOL_WEB/PHP_AI/macro1_lect_1_30.pdf'),
#   purpose='assistants'
# )

# print(file.id)

print(client.files.retrieve('file-JCrd36MHCp3ohcdS1V225dkI'))

# assist = client.

# response = openai.File.create(
#   file=Path('/Applications/MAMP/htdocs/GOL_WEB/PHP_AI/macro1_lect_1_30.pdf', 'rb'),
#   purpose='answers'
# )
# file_id = response

# # Создаем ассистента
# assistant = openai.Assistant.create(
#   name='Генератор игровых задач по макроэкономике',
#   description='Ты находишься в игре, связанной с экономикой. Игрок ждёт от тебя, что ты придумаешь ему интересную задачу по макроэкономике (используя macro1_lect_1_30.pdf)',
#   instructions='Ты должен генерировать небольшие практикоориентированные задачи, которые будут связаны с макроэкономической теорией из macro1_lect_1_30.pdf',
#   model="gpt-4",
#   files=[{"id": file_id, "purpose": "answers"}]
# )

# assistant_id = assistant['id']

# # Создаем поток
# thread = openai.Thread.create(
#   assistant_id=assistant_id,
#   messages=[
#     {
#       "role": "user",
#       "content": "Сгенерируй задание по макроэкономике",
#       "file_ids": [file_id]
#     }
#   ]
# )

# thread_id = thread['id']

# def create_msg(user_msg):
#   openai.Thread.message_create(
#     role="user",
#     thread_id=thread_id,
#     content=user_msg
#   )

# def thread_running():
#   run = openai.Thread.run_create(
#     thread_id=thread_id,
#     assistant_id=assistant_id
#   )
#   return run['id']

# def refresh_thread(run_id):
#   run_info = openai.Thread.run_retrieve(
#     thread_id=thread_id,
#     run_id=run_id
#   )
#   return run_info

# def get_last_msgs():
#   messages = openai.Thread.message_list(
#     thread_id=thread_id
#   )
#   return messages

# def ask_generator(user_msg):
#   create_msg(user_msg)
#   run_id = thread_running()
#   time.sleep(15)
#   refresh_thread(run_id)
#   messages = get_last_msgs()
#   print(messages['data'][0]['content'])

# while True:
#   print('Запросик делайте: ')
#   user_msg = input()
#   ask_generator(user_msg)  
    # fine_tune_job = client.FineTunes.create(
#     training_file='file-nHW9wnobOGIYNCCV3s0S7TbV',
#     model="gpt-3.5-turbo-0125",  
#     n_epochs=4,
#     learning_rate_multiplier=0.1
# )




# # Получение ID задачи тонкой настройки
# fine_tune_id = fine_tune_job.id
# print(f"Fine-tune ID: {fine_tune_id}")




# completion = client.chat.completions.create(
#   model="gpt-3.5-turbo",
#   messages=[
#     {"role": "system", "content": "Ты эксперт в области экономических задач по макроэкономике по теме IS-LM-BP."},
#     {"role": "user", "content": "Сгенерируй задачу, связанную с решение задачи с использованием какого-либо шока, связанного с госзакупками."}
#   ]
# )

# print(completion.choices[0].message)