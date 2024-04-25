from TTS.api import TTS
tts = TTS("tts_models/multilingual/multi-dataset/xtts_v2", gpu=False)

# generate speech by cloning a voice using default settings
tts.tts_to_file(text="Este es un texto de ejemplo para una voz en español. En concreto es un ejemplo preentrenado con la voz de el Rubius",
                file_path="output.wav",
                speaker_wav="pregrabado.wav",
                language="es")




# Command line execution
#  tts --model_name tts_models/multilingual/multi-dataset/xtts_v2 \
#      --text "Esto es un ejemplo de un texto en español. Que tenga un buen día." \
#      --speaker_wav pruebaRubius.wav \
#      --language_idx es