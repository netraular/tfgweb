import nltk
from nltk.translate.nist_score import sentence_nist
import mysql.connector
from itertools import combinations
import matplotlib.pyplot as plt
from datetime import datetime

# Descargar recursos necesarios de NLTK si aún no lo has hecho
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

def calculate_nist_scores(llm_ref, llm_candidate, cursor):
    cursor.execute(f"SELECT answer FROM LlmTestAnswers WHERE llm = '{llm_ref}'")
    correct_answers = [row[0] for row in cursor.fetchall() if row[0] is not None]

    cursor.execute(f"SELECT answer FROM LlmTestAnswers WHERE llm = '{llm_candidate}'")
    model_answers = [row[0] for row in cursor.fetchall() if row[0] is not None]

    # Tokenización de respuestas
    tokenized_references = [nltk.word_tokenize(ref) for ref in correct_answers]
    tokenized_candidates = [nltk.word_tokenize(candidate) for candidate in model_answers]

    # Calcular NIST para cada respuesta
    nist_scores = []
    for ref_tokens, cand_tokens in zip(tokenized_references, tokenized_candidates):
        if len(ref_tokens) > 0 and len(cand_tokens) > 0:
            try:
                score = sentence_nist([ref_tokens], cand_tokens)
                nist_scores.append(score)
            except ZeroDivisionError:
                # En caso de que haya un problema con la división, agrega un puntaje de 0
                nist_scores.append(0)

    # Calcular el NIST promedio
    average_nist = sum(nist_scores) / len(nist_scores) if len(nist_scores) > 0 else 0

    return average_nist

# Obtener todos los valores distintos de llm
def get_distinct_llms(cursor):
    cursor.execute("SELECT DISTINCT llm FROM LlmTestAnswers")
    llms = [row[0] for row in cursor.fetchall()]
    return llms

# Función principal
def main():
    # Conexión a la base de datos
    conn = mysql.connector.connect(**config)
    cursor = conn.cursor()

    nist_scores = {}

    try:
        # Obtener todos los llms distintos
        llms = get_distinct_llms(cursor)
        
        # Calcular NIST score para todas las combinaciones de llms (excluyendo combinaciones consigo mismo)
        for llm_ref, llm_candidate in combinations(llms, 2):
            average_nist = calculate_nist_scores(llm_ref, llm_candidate, cursor)
            nist_scores[(llm_ref, llm_candidate)] = average_nist

    finally:
        cursor.close()
        conn.close()

    # Crear la gráfica de comparación
    create_nist_score_plot(nist_scores)

def create_nist_score_plot(nist_scores):
    # Preparar los datos y ordenar de mayor a menor NIST Score
    sorted_pairs = sorted(nist_scores.items(), key=lambda x: x[1], reverse=True)
    sorted_llms = [pair[0] for pair in sorted_pairs]
    sorted_scores = [pair[1] for pair in sorted_pairs]

    # Crear la gráfica de barras
    fig, ax = plt.subplots(figsize=(10, 6))

    # Dibujar las barras ordenadas
    for i, (pair, score) in enumerate(zip(sorted_llms, sorted_scores)):
        llm1, llm2 = pair
        ax.barh(f'{llm1} vs {llm2}', score, color='lightgreen')

    # Configurar el aspecto de la gráfica
    ax.set_xlabel('NIST Score')
    ax.set_title('Comparación de NIST Score entre modelos llm (ordenado)')
    ax.set_xlim(0, 5)  # Establecer el rango del eje x de 0 a 5 para los NIST Scores

    # Mostrar los valores en las barras
    for i, (pair, score) in enumerate(zip(sorted_llms, sorted_scores)):
        llm1, llm2 = pair
        ax.text(score + 0.1, i, f'{score:.3f}', va='center')

    # Ajustar el espacio entre subplots
    plt.tight_layout()

    # Obtener la fecha y hora actuales para el nombre del archivo
    current_datetime = datetime.now().strftime("%Y%m%d_%H%M%S")
    filename = f'/var/www/html/laravel/resources/scripts/python/evaluation/model_data/model_comparison_NIST_{current_datetime}.png'

    # Guardar la gráfica como una imagen sin mostrarla
    plt.savefig(filename)

    # Mostrar la gráfica
    plt.show()

if __name__ == "__main__":
    main()
