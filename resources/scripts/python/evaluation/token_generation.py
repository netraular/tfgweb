import mysql.connector
from collections import defaultdict

# Lee la contraseña desde el archivo
with open('/var/www/html/laravel/resources/scripts/python/evaluation/db_password', 'r') as file:
    password = file.read().strip()

# Configuración de la conexión a la base de datos
config = {
    'user': 'tfg',
    'password': password,
    'host': '127.0.0.1',
    'port': 3307,
    'database': 'tfg'
}

# Conectarse a la base de datos
try:
    conn = mysql.connector.connect(**config)
    cursor = conn.cursor(dictionary=True)

    # Consulta para obtener las filas agrupadas por 'llm' y calcular el promedio
    query = """
        SELECT llm, AVG(answerTime / WORDS_COUNT) AS avg_answerTime_per_word
        FROM (
            SELECT llm, SUM(IF(answerTime IS NULL, 0, answerTime)) AS answerTime,
                   COUNT(LENGTH(answer) - LENGTH(REPLACE(answer, ' ', '')) + 1) AS WORDS_COUNT
            FROM LlmTestAnswers
            GROUP BY llm
        ) AS T
        GROUP BY llm
    """

    cursor.execute(query)
    results = cursor.fetchall()

    for result in results:
        llm = result['llm']
        avg_answerTime_per_word = result['avg_answerTime_per_word']
        print(f"LLM: {llm}, Avg Answer Time per Word: {avg_answerTime_per_word:.2f}")

except mysql.connector.Error as err:
    print(f"Error connecting to MySQL: {err}")

finally:
    if 'cursor' in locals() and cursor is not None:
        cursor.close()
    if 'conn' in locals() and conn is not None:
        conn.close()
