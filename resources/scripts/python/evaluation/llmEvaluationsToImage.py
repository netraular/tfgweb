import matplotlib.pyplot as plt
from datetime import datetime

# Datos de ejemplo (sustituir con tus datos reales)
llms = ['complexLlmToSql', 'llama38bToSql', 'codeqwen7bToSql', 'gpt3.5']
nist_scores = {
    ('complexLlmToSql', 'complexLlmToSql'): 4.255538845705311,
    ('complexLlmToSql', 'llama38bToSql'): 1.6001111736166016,
    ('complexLlmToSql', 'codeqwen7bToSql'): 1.2396381857375651,
    ('complexLlmToSql', 'gpt3.5'): 1.3295723960532737,
    ('llama38bToSql', 'llama38bToSql'): 4.198788971559999,
    ('llama38bToSql', 'codeqwen7bToSql'): 2.115331194134674,
    ('llama38bToSql', 'gpt3.5'): 2.1757561519776862,
    ('codeqwen7bToSql', 'codeqwen7bToSql'): 4.123575640970142,
    ('codeqwen7bToSql', 'gpt3.5'): 2.374306426909357,
    ('gpt3.5', 'gpt3.5'): 4.065807778758971
}

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
filename = f'/var/www/html/laravel/resources/scripts/python/model_data/evaluation/model_comparison_NIST_{current_datetime}.png'

# Guardar la gráfica como una imagen sin mostrarla
plt.savefig(filename)

# Mostrar la gráfica
plt.show()
