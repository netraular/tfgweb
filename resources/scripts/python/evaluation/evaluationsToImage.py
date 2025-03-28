import matplotlib.pyplot as plt
import numpy as np
from datetime import datetime

# Resultados obtenidos
models = [ 'medium','small','base','tiny']
precision = [ 21.6,9.3,4.5,2.1]
# acuracy = []
# recall = []
# f1_score = []

# Configuración de la gráfica
x = np.arange(len(models))  # la posición de las etiquetas en el eje x
width = 0.35  # el ancho de las barras

fig, ax = plt.subplots(figsize=(10, 6))

# Crear barras para cada métrica
rects1 = ax.bar(x, precision, width, label='Tiempo de respuesta (segundos)')
# rects2 = ax.bar(x + width, precision, width, label='Precision')
# rects3 = ax.bar(x + 2*width, recall, width, label='Recall')
# rects4 = ax.bar(x + 3*width, f1_score, width, label='F1 Score')

# Añadir etiquetas, título y leyenda con letra más grande
ax.set_xlabel('Modelos', fontsize=16)
ax.set_ylabel('Tiempo de respuesta (segundos)', fontsize=16)
ax.set_title('Comparativa de tiempo de respuesta entre modelos de whisper', fontsize=18)
ax.set_xticks(x)
ax.set_xticklabels(models, fontsize=14)
ax.legend(fontsize=14)

# Añadir las etiquetas de los valores encima de las barras con letra más grande
def autolabel(rects):
    """Adjunta una etiqueta de texto encima de las barras, mostrando su altura."""
    for rect in rects:
        height = rect.get_height()
        ax.annotate(f'{height:.2f}',
                    xy=(rect.get_x() + rect.get_width() / 2, height),
                    xytext=(0, 3),  # 3 puntos de desplazamiento vertical
                    textcoords="offset points",
                    ha='center', va='bottom', fontsize=14)

autolabel(rects1)
# autolabel(rects2)
# autolabel(rects3)
# autolabel(rects4)

fig.tight_layout()

# Obtener la fecha y hora actuales
current_datetime = datetime.now().strftime("%Y%m%d_%H%M%S")
filename = f'/var/www/html/laravel/resources/scripts/python/evaluation/model_data/model_comparison_metrics_{current_datetime}.png'

# Guardar la gráfica como una imagen con un ancho específico
plt.savefig(filename, bbox_inches='tight', pad_inches=0.5)

# Mostrar la gráfica
plt.show()

