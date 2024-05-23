import os
import json
import fitz


def extract_text_from_pdf(pdf_path):
    doc = fitz.open(pdf_path)
    text = ""
    for page in doc:
        text += page.get_text()
    return text


current_dir = os.path.dirname(os.path.abspath(__file__))
pdf_path = os.path.join(current_dir, "macro1_lect_1_30.pdf")


lectures_text = extract_text_from_pdf(pdf_path)

lectures_text = lectures_text.replace('Лекция' , 'ЛЕКЦИЯ').replace('ЛЕКЦИИ', 'ЛЕКЦИЯ')


lectures = lectures_text.split("ЛЕКЦИЯ")


jsonl_data = []
for i, lecture in enumerate(lectures[1:], start=1): 
    lecture_title = f"Лекция {i}"
    lecture_content = lecture.strip()
    jsonl_data.append({"prompt": f"{lecture_title}\n\n", "completion": lecture_content})


jsonl_path = os.path.join(current_dir, "macro1_lect_1_30.jsonl")
with open(jsonl_path, 'w', encoding='utf-8') as jsonl_file:
    for entry in jsonl_data:
        jsonl_file.write(json.dumps(entry, ensure_ascii=False) + '\n')

print(f"JSONL file created at: {jsonl_path}")
