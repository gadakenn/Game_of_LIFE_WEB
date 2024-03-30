from flask import Flask, render_template, request, redirect, url_for
from db import UserDatabase
app = Flask(__name__)

user_db = UserDatabase()

@app.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'POST':
        nickname = request.form['nickname']
        email = request.form['email']
        password = request.form['password']
        user_db.add_user(nickname, email, password)
        return redirect(url_for('success')) 
    return render_template('registration.html')

@app.route('/success')
def success():
    return "Registration successful!"

if __name__ == '__main__':
    app.run(debug=True)


import os
BOT_TOKEN = os.environ.get('BOT_TOKEN')
SHEET_ID = os.environ.get('SHEET_ID')

OPERATORS = {'артём' : 'Арт08',
             'рияз' : '143143',
             'эд' : 'Уретра',
             'паша' : 'Шапа',
             'дима' : '4857148'}
