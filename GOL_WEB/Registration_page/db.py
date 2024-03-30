import psycopg2

class UserDatabase:
    def __init__(self):
        self.connection = psycopg2.connect(dbname='postgres', user='postgres', password='1234', host='localhost')
        self.connection.autocommit = True
        self.cursor = self.connection.cursor()

    def add_user(self, nickname, email, password, num_games=0, max_score=0):
        try:
            insert_query = """
            INSERT INTO users (nickname, email, password, num_games, max_score)
            VALUES (%s, %s, %s, %s, %s);
            """
            self.cursor.execute(insert_query, (nickname, email, password, num_games, max_score))
        except psycopg2.Error as e:
            print(f"An error occurred: {e}")

    def edit_user(self, nickname, email=None, password=None, num_games=None, max_score=None):
        try:
            update_query = "UPDATE users SET "
            params = []
            if email:
                update_query += "email = %s, "
                params.append(email)
            if password:
                update_query += "password = %s, "
                params.append(password)
            if num_games is not None:
                update_query += "num_games = %s, "
                params.append(num_games)
            if max_score is not None:
                update_query += "max_score = %s, "
                params.append(max_score)
            

            update_query = update_query.rstrip(', ')

            update_query += " WHERE nickname = %s;"
            params.append(nickname)
            
            self.cursor.execute(update_query, tuple(params))
        except psycopg2.Error as e:
            print(f"An error occurred: {e}")

    def close(self):
        self.cursor.close()
        self.connection.close()


# db = UserDatabase('yourdbname', 'youruser', 'yourpassword', 'localhost')


# db.add_user('user_nickname', 'user_email@example.com', 'user_password')


# db.edit_user('user_nickname', email='new_email@example.com', num_games=5, max_score=100)

# db.close()
