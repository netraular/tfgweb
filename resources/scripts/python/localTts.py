
import os
import sys
from TTS.api import TTS
tts = TTS("tts_models/multilingual/multi-dataset/xtts_v2", gpu=False)

# Verifica que se hayan pasado los argumentos correctos
if len(sys.argv) != 3:
    print("Uso: python3 googleTts.py 'texto' 'nombre_del_archivo_salida'")
    sys.exit(1)
# El texto a convertir en audio y el nombre del archivo de salida se pasan como argumentos
texto_para_audio = sys.argv[1]
nombre_archivo_salida = sys.argv[2]


# generate speech by cloning a voice using default settings
tts.tts_to_file(text="Este es un texto de ejemplo para una voz en español. En concreto es un ejemplo preentrenado con la voz de el Rubius",
                file_path="output.wav",
                speaker_wav="pruebaRubius.wav",
                language="es")




# Command line execution
#  tts --model_name tts_models/multilingual/multi-dataset/xtts_v2 \
#      --text "Esto es un ejemplo de un texto en español. Que tenga un buen día." \
#      --speaker_wav pruebaRubius.wav \
#      --language_idx es