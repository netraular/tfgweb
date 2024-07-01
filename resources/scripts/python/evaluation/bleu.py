import mysql.connector
import nltk
from nltk.translate.bleu_score import sentence_bleu
import re
from itertools import combinations
import matplotlib.pyplot as plt
from datetime import datetime

nltk.download('punkt')  # Descargar el tokenizer punkt si aún no lo tienes

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

def calculate_bleu_scores(llm_ref, llm_candidate, cursor):
    cursor.execute(f"SELECT answer FROM LlmTestAnswers WHERE llm = '{llm_ref}'")
    correct_answers = [row[0] for row in cursor.fetchall()]

    cursor.execute(f"SELECT answer FROM LlmTestAnswers WHERE llm = '{llm_candidate}'")
    model_answers = [row[0] for row in cursor.fetchall()]

    # Función para limpiar las respuestas SQL
    def clean_text(text):
        if isinstance(text, str):
            # Ejemplo de limpieza básica (eliminar caracteres especiales)
            text = re.sub(r'[^\w\s]', '', text)
            return text.strip()  # Eliminar espacios en blanco al principio y al final
        else:
            return ''  # Devolver una cadena vacía o manejar otro tipo de objeto según sea necesario

    # Convertir a cadenas de texto si no lo son
    correct_answers = [str(answer) for answer in correct_answers]
    model_answers = [str(answer) for answer in model_answers]

    # Ejemplo de uso después de asegurarte de que correct_answers y model_answers sean cadenas de texto
    correct_answers_cleaned = [clean_text(answer) for answer in correct_answers]
    model_answers_cleaned = [clean_text(answer) for answer in model_answers]

    # Tokenización de las respuestas limpias
    tokenized_references = [[nltk.word_tokenize(ref)] for ref in correct_answers_cleaned]
    tokenized_candidate = [nltk.word_tokenize(candidate) for candidate in model_answers_cleaned]

    # Calcular BLEU para cada respuesta
    bleu_scores = []
    for ref, cand in zip(tokenized_references, tokenized_candidate):
        score = sentence_bleu(ref, cand)
        bleu_scores.append(score)

    # Calcular el BLEU promedio
    average_bleu = sum(bleu_scores) / len(bleu_scores)

    return average_bleu

# Obtener todos los valores distintos de llm
def get_distinct_llms(cursor):
    cursor.execute("SELECT DISTINCT llm FROM LlmTestAnswers WHERE llm != 'complexLlmToSql'")
    llms = [row[0] for row in cursor.fetchall()]
    return llms

# Función principal
def main():
    # Conexión a la base de datos
    conn = mysql.connector.connect(**config)
    cursor = conn.cursor()

    bleu_scores = {}  # Diccionario para almacenar los BLEU scores

    try:
        # Obtener todos los llms distintos
        llms = get_distinct_llms(cursor)
        
        # Calcular BLEU score para todas las combinaciones de llms
        for llm_ref, llm_candidate in combinations(llms, 2):
            average_bleu = calculate_bleu_scores(llm_ref, llm_candidate, cursor)
            bleu_scores[(llm_ref, llm_candidate)] = average_bleu
            print(f"BLEU Score between '{llm_ref}' and '{llm_candidate}': {average_bleu}")

    finally:
        cursor.close()
        conn.close()
    
    return bleu_scores

if __name__ == "__main__":
    bleu_scores = main()

    # Preparar los datos y ordenar de mayor a menor BLEU Score
    sorted_pairs = sorted(bleu_scores.items(), key=lambda x: x[1], reverse=True)
    sorted_llms = [pair[0] for pair in sorted_pairs]
    sorted_scores = [pair[1] for pair in sorted_pairs]

    # Crear la gráfica de barras
    fig, ax = plt.subplots(figsize=(8, 4))

    # Dibujar las barras ordenadas
    for i, (pair, score) in enumerate(zip(sorted_llms, sorted_scores)):
        llm1, llm2 = pair
        ax.barh(f'{llm1} vs {llm2}', score, color='lightgreen')

    # Configurar el aspecto de la gráfica
    ax.set_xlabel('BLEU Score')
    ax.set_title('Comparación de BLEU Score entre modelos llm (ordenado)')
    ax.set_xlim(0, max(sorted_scores) + 0.5)  # Ajustar el rango del eje x según los BLEU Scores

    # Mostrar los valores en las barras
    for i, (pair, score) in enumerate(zip(sorted_llms, sorted_scores)):
        llm1, llm2 = pair
        ax.text(score + 0.1, i, f'{score:.3f}', va='center')

    # Ajustar el espacio entre subplots
    plt.tight_layout()

    # Obtener la fecha y hora actuales para el nombre del archivo
    current_datetime = datetime.now().strftime("%Y%m%d_%H%M%S")
    filename = f'/var/www/html/laravel/resources/scripts/python/evaluation/model_data/model_comparison_BLEU_{current_datetime}.png'

    # Guardar la gráfica como una imagen sin mostrarla
    plt.savefig(filename)

    # Mostrar la gráfica
    plt.show()
