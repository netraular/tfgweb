"""Synthesizes speech from the input string of text or ssml.
Make sure to be working in a virtual environment.

Note: ssml must be well-formed according to:
    https://www.w3.org/TR/speech-synthesis/
"""
import os
import sys
from dotenv import load_dotenv
from google.cloud import texttospeech

# Carga las variables de entorno desde el archivo .env
load_dotenv('/var/www/html/laravel/.env')
# Ahora puedes acceder a las variables de entorno como si estuvieran definidas en el entorno de ejecución
google_credentials = os.getenv('GOOGLE_APPLICATION_CREDENTIALS')
# Configura las credenciales de la API de Google Cloud
os.environ['GOOGLE_APPLICATION_CREDENTIALS'] = google_credentials

# Verifica que se hayan pasado los argumentos correctos
if len(sys.argv) != 3:
    print("Uso: python3 googleTts.py 'texto' 'nombre_del_archivo_salida'")
    sys.exit(1)

# El texto a convertir en audio y el nombre del archivo de salida se pasan como argumentos
texto_para_audio = sys.argv[1]
nombre_archivo_salida = sys.argv[2]

# Instantiates a client
client = texttospeech.TextToSpeechClient()

# Set the text input to be synthesized
synthesis_input = texttospeech.SynthesisInput(text=texto_para_audio)

# Build the voice request, select the language code ("en-US") and the ssml
# voice gender ("neutral")
voice = texttospeech.VoiceSelectionParams(
    language_code="es-ES", ssml_gender=texttospeech.SsmlVoiceGender.NEUTRAL
)

# Select the type of audio file you want returned
audio_config = texttospeech.AudioConfig(
    audio_encoding=texttospeech.AudioEncoding.MP3
)

# Perform the text-to-speech request on the text input with the selected
# voice parameters and audio file type
response = client.synthesize_speech(
    input=synthesis_input, voice=voice, audio_config=audio_config
)

# The response's audio_content is binary.
with open('/var/www/html/laravel/storage/audios/tts/'+nombre_archivo_salida+'.mp3', "wb") as out:
    # Write the response to the output file.
    out.write(response.audio_content)
