import mysql.connector
from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score

# Lee la contraseña desde el archivo
with open('/var/www/html/laravel/resources/scripts/python/evaluation/db_password', 'r') as file:
    password = file.read().strip()

# Configuración de la conexión a la base de datos
config = {
    'user': 'tfg',
    'password': password,
    'host': '127.0.0.1',
    'port': '3307',
    'database': 'tfg'
}

# Conectar a la base de datos
conn = mysql.connector.connect(**config)
cursor = conn.cursor()

try:
    # Obtener los nombres de los modelos
    cursor.execute("SELECT DISTINCT llm FROM LlmTestAnswers")
    models = [row[0] for row in cursor.fetchall()]

    for model in models:
        # Obtener las respuestas para el modelo actual, excluyendo valores NULL
        cursor.execute("SELECT isCorrect FROM LlmTestAnswers WHERE llm = %s ", (model,))
        results = [row[0] for row in cursor.fetchall() ]

        # Crear etiquetas binarias: 1 si esCorrect es 1, de lo contrario 0
        y_true = [1] * len(results)  # Todas las respuestas esperadas son correctas (1)
        y_pred = [1 if x == 1 or x==0.5 else 0 for x in results]

        # Calcular las métricas
        accuracy = accuracy_score(y_true, y_pred)
        precision = precision_score(y_true, y_pred, zero_division=0)
        recall = recall_score(y_true, y_pred, zero_division=0)
        f1 = f1_score(y_true, y_pred, zero_division=0)

        print(f"Metrics for model {model}:")
        print(f"Accuracy: {accuracy:.2f}")
        print(f"Precision: {precision:.2f}")
        print(f"Recall: {recall:.2f}")
        print(f"F1 Score: {f1:.2f}")
        print()

finally:
    # Cerrar cursor y conexión
    cursor.close()
    conn.close()
