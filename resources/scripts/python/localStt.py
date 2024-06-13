
import os
import sys
import whisper

model = whisper.load_model("small")

# Especificar el idioma de transcripción
options = {
    "language": "es"  # Código de idioma ISO 639-1 para español
}


# Verifica que se hayan pasado los argumentos correctos
if len(sys.argv) != 2:
    print("Uso: python3 localStt.py 'path_y_nombre_del_archivo_audio'")
    sys.exit(1)

# El texto a convertir en audio y el nombre del archivo de salida se pasan como argumentos
audio_para_texto = sys.argv[1]


# Opcional: Below is an example usage of whisper.detect_language() and whisper.decode() which provide lower-level access to the model.
# # load audio and pad/trim it to fit 30 seconds
# audio = whisper.load_audio("audio.mp3")
# audio = whisper.pad_or_trim(audio)

# # make log-Mel spectrogram and move to the same device as the model
# mel = whisper.log_mel_spectrogram(audio).to(model.device)

# # detect the spoken language
# _, probs = model.detect_language(mel)
# print(f"Detected language: {max(probs, key=probs.get)}")

# # decode the audio
# options = whisper.DecodingOptions()
# result = whisper.decode(model, mel, options)


# Realizar la transcripción especificando el idioma
file_name = "/var/www/html/laravel/storage/"+audio_para_texto
result = model.transcribe(file_name, **options)
print(result["text"])