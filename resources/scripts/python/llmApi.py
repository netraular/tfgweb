import os
import sys
import asyncio
from ollama import AsyncClient
from ollama import Client

# Verifica que se hayan pasado los argumentos correctos
if len(sys.argv) != 3:
    print("Uso: python3 llmApi.py 'question' 'llmName'")
    sys.exit(1)
# El texto a convertir en audio y el nombre del archivo de salida se pasan como argumentos
question = sys.argv[1]
llmName = sys.argv[2]

client = Client(host='http://localhost:11434')
response = client.chat(model=llmName, messages=[
  {
    'role': 'user',
    'content': question,
    'keep_alive':-1,
  },
])

print(response["message"]["content"])
