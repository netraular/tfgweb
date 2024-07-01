import nltk
from nltk.translate import meteor_score
import mysql.connector
from itertools import combinations
import matplotlib.pyplot as plt
from datetime import datetime

# Descargar recursos necesarios de NLTK si aún no lo has hecho
nltk.download('wordnet')
nltk.download('punkt')

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

def calculate_meteor_scores(llm_ref, llm_candidate, cursor):
    cursor.execute(f"SELECT answer FROM LlmTestAnswers WHERE llm = '{llm_ref}'")
    correct_answers = [row[0] for row in cursor.fetchall() if row[0] is not None]

    cursor.execute(f"SELECT answer FROM LlmTestAnswers WHERE llm = '{llm_candidate}'")
    model_answers = [row[0] for row in cursor.fetchall() if row[0] is not None]

    # Tokenización de respuestas
    tokenized_references = [nltk.word_tokenize(ref) for ref in correct_answers]
    tokenized_candidate = [nltk.word_tokenize(candidate) for candidate in model_answers]

    # Calcular METEOR para cada respuesta
    meteor_scores = []
    for ref_tokens, cand_tokens in zip(tokenized_references, tokenized_candidate):
        score = meteor_score.single_meteor_score(ref_tokens, cand_tokens)
        meteor_scores.append(score)

    # Calcular el METEOR promedio
    average_meteor = sum(meteor_scores) / len(meteor_scores) if len(meteor_scores) > 0 else 0

    return average_meteor

# Obtener todos los valores distintos de llm
def get_distinct_llms(cursor):
    cursor.execute("SELECT DISTINCT llm FROM LlmTestAnswers WHERE llm != 'complexLlmToSql'")
    llms = [row[0] for row in cursor.fetchall()]
    return llms

# Crear la gráfica de resultados
def create_graph(llm_pairs, meteor_scores):
    # Preparar los datos y ordenar de mayor a menor METEOR Score
    sorted_pairs = sorted(zip(llm_pairs, meteor_scores), key=lambda x: x[1], reverse=True)
    sorted_llms = [pair[0] for pair in sorted_pairs]
    sorted_scores = [pair[1] for pair in sorted_pairs]

    # Crear la gráfica de barras
    fig, ax = plt.subplots(figsize=(8, 4))

    # Dibujar las barras ordenadas
    for i, (pair, score) in enumerate(zip(sorted_llms, sorted_scores)):
        llm1, llm2 = pair
        ax.barh(f'{llm1} vs {llm2}', score, color='lightgreen')

    # Configurar el aspecto de la gráfica
    ax.set_xlabel('METEOR Score')
    ax.set_title('Comparación de METEOR Score entre modelos LLM (ordenado)')
    ax.set_xlim(0, 1)  # Establecer el rango del eje x de 0 a 1 para los METEOR Scores

    # Mostrar los valores en las barras
    for i, (pair, score) in enumerate(zip(sorted_llms, sorted_scores)):
        llm1, llm2 = pair
        ax.text(score + 0.01, i, f'{score:.3f}', va='center')

    # Ajustar el espacio entre subplots
    plt.tight_layout()

    # Obtener la fecha y hora actuales para el nombre del archivo
    current_datetime = datetime.now().strftime("%Y%m%d_%H%M%S")
    filename = f'/var/www/html/laravel/resources/scripts/python/evaluation/model_data/model_comparison_METEOR_{current_datetime}.png'

    # Guardar la gráfica como una imagen sin mostrarla
    plt.savefig(filename)

    # Mostrar la gráfica
    plt.show()

# Función principal
def main():
    # Conexión a la base de datos
    conn = mysql.connector.connect(**config)
    cursor = conn.cursor()

    try:
        # Obtener todos los llms distintos
        llms = get_distinct_llms(cursor)

        # Calcular METEOR score para todas las combinaciones de llms
        llm_pairs = []
        meteor_scores = []
        for llm_ref, llm_candidate in combinations(llms, 2):
            average_meteor = calculate_meteor_scores(llm_ref, llm_candidate, cursor)
            llm_pairs.append((llm_ref, llm_candidate))
            meteor_scores.append(average_meteor)
            print(f"METEOR Score between '{llm_ref}' and '{llm_candidate}': {average_meteor}")

        # Crear la gráfica con los resultados
        create_graph(llm_pairs, meteor_scores)

    finally:
        cursor.close()
        conn.close()

if __name__ == "__main__":
    main()
