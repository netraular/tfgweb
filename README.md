
# Local Voice Assistant Natural Language to SQL

This project is a comprehensive local voice assistant system, developed as a final year project for a degree in computer engineering. The main goal of the project is to provide a robust and efficient voice assistant that operates entirely offline, ensuring user privacy and data security.

The system consists of several key components including a web interface, a speech-to-text (STT) module, a text-to-speech (TTS) module, and natural language processing to SQL query conversion. These components work together to allow users to interact with the assistant using voice commands, which are then processed to perform various tasks such as querying a database.


## Technologies Used

**Web Interface - Laravel** 

The web interface is built using Laravel, a PHP framework known for its elegant syntax and powerful tools for web application development. Laravel handles the backend operations, serving web pages, managing user inputs, and communicating with the other components of the system.

**Speech-to-Text (STT) - WhisperAI** 

For converting speech to text, we use WhisperAI, an open-source model developed by OpenAI. WhisperAI is chosen for its high accuracy and capability to run efficiently on local hardware, ensuring all voice processing is done offline to protect user privacy.

**Text-to-Speech (TTS) - CoquiTTS** 

The text-to-speech functionality is powered by CoquiTTS with the XTTS v2 model. This module converts textual responses from the system into natural-sounding speech, enhancing user interaction with the assistant.

**Natural Language to SQL - Ollama with CodeQwen1.5** 

The conversion of natural language queries to SQL commands is handled by CodeQwen1.5, operated through Ollama. This allows users to ask questions in plain language and receive accurate, relevant data from the database.

**Other Technologies** 

**Bootstrap:** For the frontend design, ensuring a responsive and user-friendly interface.

**MySQL:** Used as the database management system to store and retrieve data efficiently.


## Project Structure

The project is organized into the following directories:

* /: Contains the Laravel web application code.
* /resources/scripts/python: Python scripts for STT and TTS functionalities.
* /resources/scripts/python/evaluation: Python scripts for creating llm evaluations.
* /resources/llmModels: Pre-trained models for WhisperAI and CoquiTTS.
* /resources/scripts/db: Database files and SQL scripts for setting up MySQL.
## Installation

**Prerequisites**

PHP and Composer installed for Laravel.

Python and pip for running scripts.

MySQL server for database management.

**1-Clone the repository:**

```bash
git clone https://github.com/username/local-voice-assistant.git
cd local-voice-assistant
```

**2-Install Laravel dependencies:**

```bash
cd web
composer install
```
    
**3-Set up the environment file:**

```bash
cp .env.example .env
```
Update the .env file with your database credentials and other configurations.

**4-Install Python dependencies:**
```bash
pip install -r requirements.txt
```
**5-Set up the database:**
```bash
mysql -u root -p < data/database.sql
```
**6-Run the Laravel server:**
```bash
php artisan serve
```
## Usage/Examples

Once the setup is complete, navigate to http://localhost:8000 to access the web interface. The interface allows you to:

**Test STT and TTS**: Upload audio files or record voice commands directly in the browser.

**Perform SQL queries**: Use natural language to query the database and get results.

**View query history**: Check previous interactions and their outcomes.



## License

This project is licensed under the [MIT License](https://choosealicense.com/licenses/mit/).

