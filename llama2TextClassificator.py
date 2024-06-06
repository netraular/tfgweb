from lamini import LaminiClassifier
llm = LaminiClassifier()

prompts={
  "cat": "Cats are generally more independent and aloof than dogs, who are often more social and affectionate. Cats are also more territorial and may be more aggressive when defending their territory.  Cats are self-grooming animals, using their tongues to keep their coats clean and healthy. Cats use body language and vocalizations, such as meowing and purring, to communicate.",
  "dog": "Dogs are more pack-oriented and tend to be more loyal to their human family.  Dogs, on the other hand, often require regular grooming from their owners, including brushing and bathing. Dogs use body language and barking to convey their messages. Dogs are also more responsive to human commands and can be trained to perform a wide range of tasks.",
}

llm.prompt_train(prompts)

llm.save("models/my_model.lamini")
llm.predict(["meow"])
