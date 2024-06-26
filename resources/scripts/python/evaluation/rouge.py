import mysql.connector
from itertools import combinations
from rouge_score import rouge_scorer
import matplotlib.pyplot as plt
from datetime import datetime

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

def calculate_rouge_scores(llm_ref, llm_candidate, cursor):
    cursor.execute(f"SELECT answer FROM LlmTestAnswers WHERE llm = '{llm_ref}'")
    correct_answers = [row[0] for row in cursor.fetchall() if row[0] is not None]

    cursor.execute(f"SELECT answer FROM LlmTestAnswers WHERE llm = '{llm_candidate}'")
    model_answers = [row[0] for row in cursor.fetchall() if row[0] is not None]

    # Inicializar el calculador de ROUGE
    scorer = rouge_scorer.RougeScorer(['rouge1', 'rouge2', 'rougeL'], use_stemmer=True)

    # Calcular ROUGE para cada respuesta
    rouge1_scores, rouge2_scores, rougeL_scores = [], [], []
    for ref, cand in zip(correct_answers, model_answers):
        scores = scorer.score(ref, cand)
        rouge1_scores.append(scores['rouge1'].fmeasure)
        rouge2_scores.append(scores['rouge2'].fmeasure)
        rougeL_scores.append(scores['rougeL'].fmeasure)

    # Calcular los ROUGE promedios
    average_rouge1 = sum(rouge1_scores) / len(rouge1_scores) if len(rouge1_scores) > 0 else 0
    average_rouge2 = sum(rouge2_scores) / len(rouge2_scores) if len(rouge2_scores) > 0 else 0
    average_rougeL = sum(rougeL_scores) / len(rougeL_scores) if len(rougeL_scores) > 0 else 0

    return average_rouge1, average_rouge2, average_rougeL

# Obtener todos los valores distintos de llm
def get_distinct_llms(cursor):
    cursor.execute("SELECT DISTINCT llm FROM LlmTestAnswers")
    llms = [row[0] for row in cursor.fetchall()]
    return llms

# Crear una función para generar la gráfica de barras para una métrica ROUGE específica
def plot_rouge_scores(rouge_scores, rouge_type):
    sorted_pairs = sorted(rouge_scores[rouge_type].items(), key=lambda x: x[1], reverse=True)
    sorted_llms = [f'{pair[0][0]} vs {pair[0][1]}' for pair in sorted_pairs]
    sorted_scores = [pair[1] for pair in sorted_pairs]

    # Crear la gráfica de barras
    fig, ax = plt.subplots(figsize=(10, 6))

    # Dibujar las barras ordenadas
    ax.barh(sorted_llms, sorted_scores, color='lightblue')

    # Configurar el aspecto de la gráfica
    ax.set_xlabel(f'{rouge_type} Score')
    ax.set_title(f'Comparación de {rouge_type} Score entre modelos llm (ordenado)')
    ax.set_xlim(0, max(sorted_scores) + 0.1)  # Establecer el rango del eje x

    # Mostrar los valores en las barras
    for i, score in enumerate(sorted_scores):
        ax.text(score + 0.01, i, f'{score:.3f}', va='center')

    # Ajustar el espacio entre subplots
    plt.tight_layout()

    # Obtener la fecha y hora actuales para el nombre del archivo
    current_datetime = datetime.now().strftime("%Y%m%d_%H%M%S")
    filename = f'/var/www/html/laravel/resources/scripts/python/evaluation/model_data/model_comparison_{rouge_type}_{current_datetime}.png'

    # Guardar la gráfica como una imagen sin mostrarla
    plt.savefig(filename)

    # Mostrar la gráfica
    plt.show()

# Función principal
def main():
    # Conexión a la base de datos
    conn = mysql.connector.connect(**config)
    cursor = conn.cursor()

    rouge_scores = {
        'ROUGE-1': {},
        'ROUGE-2': {},
        'ROUGE-L': {}
    }

    try:
        # Obtener todos los llms distintos
        llms = get_distinct_llms(cursor)
        
        # Calcular ROUGE score para todas las combinaciones de llms
        for llm_ref, llm_candidate in combinations(llms, 2):
            average_rouge1, average_rouge2, average_rougeL = calculate_rouge_scores(llm_ref, llm_candidate, cursor)
            rouge_scores['ROUGE-1'][(llm_ref, llm_candidate)] = average_rouge1
            rouge_scores['ROUGE-2'][(llm_ref, llm_candidate)] = average_rouge2
            rouge_scores['ROUGE-L'][(llm_ref, llm_candidate)] = average_rougeL

            print(f"ROUGE-1 Score between '{llm_ref}' and '{llm_candidate}': {average_rouge1}")
            print(f"ROUGE-2 Score between '{llm_ref}' and '{llm_candidate}': {average_rouge2}")
            print(f"ROUGE-L Score between '{llm_ref}' and '{llm_candidate}': {average_rougeL}")

    finally:
        cursor.close()
        conn.close()

    # Generar gráficas para ROUGE-1, ROUGE-2 y ROUGE-L
    for rouge_type in rouge_scores:
        plot_rouge_scores(rouge_scores, rouge_type)

if __name__ == "__main__":
    main()
