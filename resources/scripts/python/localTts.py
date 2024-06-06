
import os
import sys
import numba
from TTS.api import TTS

numba.config.DISABLE_CACHING = True

tts = TTS("tts_models/multilingual/multi-dataset/xtts_v2", gpu=False)

# Verifica que se hayan pasado los argumentos correctos
if len(sys.argv) != 4:
    print("Uso: python3 localTts.py 'texto' 'nombre_del_archivo_salida' 'idioma'")
    sys.exit(1)
# El texto a convertir en audio y el nombre del archivo de salida se pasan como argumentos
texto_para_audio = sys.argv[1]
nombre_archivo_salida = sys.argv[2]
idioma = sys.argv[3]


# generate speech by cloning a voice using default settings
tts.tts_to_file(text=texto_para_audio,
                file_path="/var/www/html/laravel/storage/audios/tts/localTts/"+nombre_archivo_salida+".mp3",
                speaker_wav="/var/www/html/laravel/storage/audios/tts/audio_raul.wav",
                language=idioma)




# Command line execution
#  tts --model_name tts_models/multilingual/multi-dataset/xtts_v2 \
#      --text "Esto es un ejemplo de un texto en español. Que tenga un buen día." \
#      --speaker_wav pruebaRubius.wav \
#      --language_idx es