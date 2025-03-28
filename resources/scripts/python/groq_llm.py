import os
import sys
from groq import Groq

# Verifica que se haya pasado UN argumento (la pregunta)
if len(sys.argv) != 2:
    sys.stderr.write("Uso: python3 groq_llm.py 'question'\n")
    sys.exit(1)

question = sys.argv[1]
api_key = os.environ.get("GROQ_API_KEY")

if not api_key:
    sys.stderr.write("Error: La variable de entorno GROQ_API_KEY no está configurada.\n")
    sys.exit(1)

# Define el system prompt
system_prompt = """You are a machine that can only answer with a sql query.You cannot say anything that isn't a sql query. If the question is not related to any table or column of the given database you say "I don't know.". You answer all the questions with the knowledge of only this database:
CREATE TABLE `Material` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `cantidad` int unsigned DEFAULT NULL,
  `proyecto` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Material_FK` (`proyecto`),
  CONSTRAINT `Material_FK` FOREIGN KEY (`proyecto`) REFERENCES `Proyecto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `Proyecto` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `sector` varchar(100) DEFAULT NULL,
  `ubicacion` varchar(100) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `Trabajador` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `apellidos` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `edad` smallint unsigned DEFAULT NULL,
  `sexo` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `TrabajadoresDelProyecto` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trabajador` bigint unsigned NOT NULL,
  `proyecto` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `trabajadores_del_proyecto_FK` (`trabajador`),
  KEY `trabajadores_del_proyecto_FK_1` (`proyecto`),
  CONSTRAINT `trabajadores_del_proyecto_FK` FOREIGN KEY (`trabajador`) REFERENCES `Trabajador` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `trabajadores_del_proyecto_FK_1` FOREIGN KEY (`proyecto`) REFERENCES `Proyecto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;"""

try:
    client = Groq(api_key=api_key)

    chat_completion = client.chat.completions.create(
        messages=[
            # 1. Añade el mensaje del sistema aquí
            {"role": "system", "content": system_prompt},
            # 2. Luego el mensaje del usuario
            {"role": "user", "content": question}
        ],
        model="llama3-70b-8192",
    )

    # Solo imprimir el contenido en stdout
    print(chat_completion.choices[0].message.content)
    sys.stdout.flush()

except Exception as e:
    # Imprimir el error en stderr y salir con código de error
    sys.stderr.write(f"Error en la API de Groq: {str(e)}\n")
    sys.exit(1)