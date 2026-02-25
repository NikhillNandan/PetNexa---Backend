from class_names import CLASS_NAMES

BREED_PROPS = {
    "cat - abyssinian": ("medium", "very_high", "short", ["playful", "curious", "active", "social"], ["climbing"]),
    "cat - american shorthair": ("medium", "moderate", "short", ["easygoing", "gentle", "playful", "adaptable"], []),
    "cat - bengal": ("medium", "very_high", "short", ["energetic", "curious", "intelligent", "vocal"], ["climbing", "water_lover"]),
    "cat - birman": ("medium", "moderate", "long", ["gentle", "affectionate", "quiet", "social"], []),
    "cat - bombay": ("medium", "moderate", "short", ["affectionate", "playful", "social", "lap_cat"], []),
    "cat - british shorthair": ("large", "low", "short", ["calm", "easygoing", "independent", "gentle"], ["obesity_prone"]),
    "cat - egyptian mau": ("medium", "high", "short", ["active", "loyal", "playful", "fast"], []),
    "cat - maine coon": ("giant", "moderate", "long", ["gentle", "playful", "social", "intelligent"], ["hip_dysplasia"]),
    "cat - persian": ("medium", "low", "long", ["calm", "gentle", "quiet", "affectionate"], ["brachycephalic", "eye_care"]),
    "cat - ragdoll": ("large", "low", "long", ["docile", "calm", "affectionate", "gentle"], []),
    "cat - russian blue": ("medium", "moderate", "short", ["shy", "gentle", "quiet", "loyal"], []),
    "cat - scottishfold": ("medium", "moderate", "short", ["sweet", "gentle", "adaptable", "quiet"], ["joint_issues"]),
    "cat - siamese": ("medium", "high", "short", ["vocal", "social", "intelligent", "affectionate"], []),
    "cat - sphinx": ("medium", "high", "hairless", ["energetic", "social", "affectionate", "attention_seeking"], ["skin_care", "temperature_sensitive"]),
    "cat - sphynx": ("medium", "high", "hairless", ["energetic", "social", "affectionate", "attention_seeking"], ["skin_care", "temperature_sensitive"]),

    "dog - afghan": ("large", "high", "long", ["dignified", "independent", "aloof", "loyal"], []),
    "dog - african wild dog": ("large", "very_high", "short", ["social", "energetic", "pack_animal", "active"], ["exotic"]),
    "dog - airedale": ("large", "high", "wiry", ["confident", "intelligent", "courageous", "friendly"], []),
    "dog - akita": ("giant", "moderate", "double", ["loyal", "dignified", "courageous", "protective"], ["hip_dysplasia"]),
    "dog - american hairless": ("small", "moderate", "hairless", ["playful", "energetic", "alert", "curious"], ["skin_care", "temperature_sensitive"]),
    "dog - american spaniel": ("medium", "high", "medium", ["friendly", "gentle", "smart", "eager"], []),
    "dog - aspin": ("medium", "moderate", "short", ["loyal", "adaptable", "intelligent", "friendly"], []),
    "dog - basenji": ("medium", "high", "short", ["independent", "intelligent", "alert", "curious"], ["barkless"]),
    "dog - basset": ("medium", "low", "short", ["patient", "gentle", "devoted", "stubborn"], ["ear_care", "obesity_prone"]),
    "dog - beagle": ("medium", "high", "short", ["friendly", "curious", "merry", "determined"], ["obesity_prone", "ear_care"]),
    "dog - bearded collie": ("large", "high", "long", ["lively", "smart", "active", "affectionate"], []),
    "dog - bermaise": ("giant", "moderate", "long", ["gentle", "calm", "loyal", "affectionate"], ["hip_dysplasia", "short_lifespan"]),
    "dog - bernard-dog - saint": ("giant", "low", "long", ["gentle", "patient", "friendly", "watchful"], ["hip_dysplasia", "drool", "short_lifespan"]),
    "dog - bernese mountain": ("giant", "moderate", "long", ["gentle", "calm", "loyal", "affectionate"], ["hip_dysplasia", "short_lifespan"]),
    "dog - bichon frise": ("small", "high", "curly", ["playful", "cheerful", "gentle", "affectionate"], ["hypoallergenic"]),
    "dog - blenheim": ("small", "moderate", "long", ["gentle", "affectionate", "graceful", "playful"], ["heart_issues"]),
    "dog - bloodhound": ("giant", "moderate", "short", ["gentle", "patient", "stubborn", "affectionate"], ["ear_care", "drool"]),
    "dog - bluetick": ("large", "high", "short", ["friendly", "intelligent", "active", "devoted"], ["ear_care"]),
    "dog - border collie": ("medium", "very_high", "medium", ["intelligent", "energetic", "alert", "responsive"], []),
    "dog - border-dog - collie": ("medium", "very_high", "medium", ["intelligent", "energetic", "alert", "responsive"], []),
    "dog - borzoi": ("large", "moderate", "long", ["gentle", "independent", "quiet", "athletic"], []),
    "dog - boston terrier": ("small", "moderate", "short", ["friendly", "lively", "intelligent", "affectionate"], ["brachycephalic"]),
    "dog - boxer": ("large", "high", "short", ["playful", "loyal", "energetic", "intelligent"], ["brachycephalic", "cancer_prone"]),
    "dog - bull mastiff": ("giant", "moderate", "short", ["loyal", "brave", "calm", "protective"], ["hip_dysplasia", "drool", "brachycephalic"]),
    "dog - bull terrier": ("medium", "high", "short", ["playful", "energetic", "courageous", "loyal"], []),
    "dog - bulldog": ("medium", "low", "short", ["calm", "gentle", "friendly", "loyal"], ["brachycephalic", "obesity_prone", "skin_folds"]),
    "dog - bulldong-dog - french": ("small", "moderate", "short", ["playful", "adaptable", "affectionate", "alert"], ["brachycephalic", "skin_folds"]),
    "dog - cairn": ("small", "high", "wiry", ["alert", "cheerful", "hardy", "active"], []),
    "dog - cavalier-dog - charles-dog - king-dog - spaniel": ("small", "moderate", "long", ["gentle", "affectionate", "graceful", "playful"], ["heart_issues"]),
    "dog - chihuahua": ("tiny", "moderate", "short", ["alert", "lively", "confident", "devoted"], ["dental_issues", "temperature_sensitive"]),
    "dog - chinese crested": ("tiny", "moderate", "hairless", ["affectionate", "alert", "playful", "lively"], ["skin_care", "temperature_sensitive", "dental_issues"]),
    "dog - chow": ("large", "low", "double", ["dignified", "independent", "loyal", "quiet"], []),
    "dog - clumber": ("large", "low", "medium", ["gentle", "loyal", "calm", "affectionate"], ["obesity_prone"]),
    "dog - coated-dog - flat-dog - retriever": ("large", "high", "medium", ["friendly", "optimistic", "confident", "devoted"], []),
    "dog - cockapoo": ("small", "high", "curly", ["friendly", "affectionate", "intelligent", "outgoing"], ["hypoallergenic"]),
    "dog - cocker": ("medium", "high", "medium", ["happy", "gentle", "smart", "merry"], ["ear_care"]),
    "dog - collie": ("large", "high", "long", ["loyal", "gentle", "intelligent", "graceful"], []),
    "dog - corgi": ("small", "high", "double", ["playful", "bold", "friendly", "intelligent"], ["back_issues", "obesity_prone"]),
    "dog - coyote": ("medium", "very_high", "medium", ["wild", "alert", "adaptable", "intelligent"], ["exotic"]),
    "dog - dachshund": ("small", "moderate", "short", ["clever", "stubborn", "devoted", "lively"], ["back_issues"]),
    "dog - dalmatian": ("large", "very_high", "short", ["energetic", "playful", "sensitive", "outgoing"], ["deafness_prone"]),
    "dog - dhole": ("medium", "very_high", "short", ["social", "pack_animal", "wild", "intelligent"], ["exotic"]),
    "dog - dingo": ("medium", "very_high", "short", ["independent", "alert", "intelligent", "wild"], ["exotic"]),
    "dog - doberman": ("large", "high", "short", ["loyal", "alert", "fearless", "intelligent"], []),
    "dog - doberman-dog - pinscher": ("large", "high", "short", ["loyal", "alert", "fearless", "intelligent"], []),
    "dog - elk hound": ("medium", "high", "double", ["bold", "hardy", "loyal", "friendly"], []),
    "dog - french bulldog": ("small", "moderate", "short", ["playful", "adaptable", "affectionate", "alert"], ["brachycephalic", "skin_folds"]),
    "dog - german sheperd": ("large", "high", "double", ["loyal", "confident", "courageous", "intelligent"], ["hip_dysplasia"]),
    "dog - german-dog - sheperd": ("large", "high", "double", ["loyal", "confident", "courageous", "intelligent"], ["hip_dysplasia"]),
    "dog - golden retriever": ("large", "high", "long", ["friendly", "reliable", "trustworthy", "intelligent"], []),
    "dog - golden-dog - retriever": ("large", "high", "long", ["friendly", "reliable", "trustworthy", "intelligent"], []),
    "dog - great dane": ("giant", "moderate", "short", ["friendly", "patient", "dependable", "gentle"], ["hip_dysplasia", "short_lifespan"]),
    "dog - great perenees": ("giant", "moderate", "long", ["calm", "patient", "gentle", "protective"], ["hip_dysplasia"]),
    "dog - greyhound": ("large", "moderate", "short", ["gentle", "independent", "quiet", "athletic"], []),
    "dog - groenendael": ("large", "high", "long", ["alert", "loyal", "intelligent", "protective"], []),
    "dog - havanese": ("small", "moderate", "long", ["playful", "gentle", "affectionate", "intelligent"], ["hypoallergenic"]),
    "dog - husky-dog - siberian": ("medium", "very_high", "double", ["outgoing", "mischievous", "loyal", "friendly"], ["escape_artist"]),
    "dog - irish spaniel": ("large", "high", "curly", ["playful", "brave", "hardworking", "active"], ["hypoallergenic"]),
    "dog - irish wolfhound": ("giant", "moderate", "wiry", ["gentle", "noble", "calm", "courageous"], ["short_lifespan"]),
    "dog - japanese spaniel": ("tiny", "moderate", "long", ["alert", "loyal", "aristocratic", "loving"], []),
    "dog - komondor": ("giant", "moderate", "long", ["steady", "loyal", "dignified", "protective"], ["coat_cording"]),
    "dog - labradoodle": ("large", "high", "curly", ["friendly", "energetic", "sociable", "intelligent"], ["hypoallergenic"]),
    "dog - labrador": ("large", "high", "short", ["friendly", "outgoing", "active", "gentle"], ["obesity_prone"]),
    "dog - labrador-dog - retriever": ("large", "high", "short", ["friendly", "outgoing", "active", "gentle"], ["obesity_prone"]),
    "dog - lhasa": ("small", "moderate", "long", ["confident", "smart", "comical", "loyal"], []),
    "dog - malinois": ("large", "very_high", "short", ["confident", "hardworking", "protective", "intelligent"], []),
    "dog - maltese": ("tiny", "moderate", "long", ["gentle", "playful", "charming", "affectionate"], ["hypoallergenic", "dental_issues"]),
    "dog - mex hairless": ("medium", "moderate", "hairless", ["calm", "alert", "loyal", "tranquil"], ["skin_care", "temperature_sensitive"]),
    "dog - miniature-dog - schnauzer": ("small", "high", "wiry", ["friendly", "smart", "obedient", "alert"], ["hypoallergenic"]),
    "dog - newfoundland": ("giant", "moderate", "long", ["sweet", "patient", "gentle", "devoted"], ["drool", "hip_dysplasia"]),
    "dog - pekinese": ("small", "low", "long", ["loyal", "affectionate", "regal", "confident"], ["brachycephalic"]),
    "dog - pit bull": ("large", "high", "short", ["loyal", "courageous", "friendly", "determined"], []),
    "dog - pomeranian": ("tiny", "high", "double", ["lively", "bold", "inquisitive", "playful"], ["dental_issues"]),
    "dog - poodle": ("medium", "high", "curly", ["intelligent", "active", "elegant", "alert"], ["hypoallergenic"]),
    "dog - pug": ("small", "low", "short", ["charming", "mischievous", "loving", "sociable"], ["brachycephalic", "obesity_prone", "eye_care"]),
    "dog - rhodesian": ("large", "high", "short", ["dignified", "even_tempered", "loyal", "athletic"], []),
    "dog - rottweiler": ("large", "moderate", "short", ["loyal", "confident", "courageous", "calm"], ["hip_dysplasia"]),
    "dog - saint bernard": ("giant", "low", "long", ["gentle", "patient", "friendly", "watchful"], ["hip_dysplasia", "drool", "short_lifespan"]),
    "dog - schnauzer": ("medium", "high", "wiry", ["friendly", "smart", "fearless", "spirited"], ["hypoallergenic"]),
    "dog - scotch terrier": ("small", "moderate", "wiry", ["independent", "confident", "spirited", "dignified"], []),
    "dog - shar pei": ("medium", "moderate", "short", ["loyal", "independent", "calm", "devoted"], ["skin_folds", "eye_care"]),
    "dog - sheepdog-dog - shetland": ("small", "high", "long", ["intelligent", "playful", "energetic", "loyal"], []),
    "dog - shiba inu": ("medium", "high", "double", ["alert", "active", "bold", "good_natured"], []),
    "dog - shih-dog - tzu": ("small", "moderate", "long", ["affectionate", "playful", "outgoing", "loyal"], ["brachycephalic"]),
    "dog - shih-tzu": ("small", "moderate", "long", ["affectionate", "playful", "outgoing", "loyal"], ["brachycephalic"]),
    "dog - siberian husky": ("medium", "very_high", "double", ["outgoing", "mischievous", "loyal", "friendly"], ["escape_artist"]),
    "dog - vizsla": ("large", "very_high", "short", ["gentle", "energetic", "affectionate", "loyal"], []),
    "dog - yorkie": ("tiny", "moderate", "long", ["affectionate", "sprightly", "tomboyish", "bold"], ["dental_issues", "hypoallergenic"]),
}


def get_display_name(breed_key):
    parts = breed_key.split(" - ")
    animal = parts[0].strip().capitalize()
    breed_parts = [p.strip().title() for p in parts[1:] if p.strip().lower() not in ("dog", "cat")]
    breed_name = " ".join(breed_parts) if breed_parts else parts[-1].strip().title()
    return animal, breed_name


def get_food_recommendations(animal, breed_name, size, energy, special):
    is_cat = animal == "Cat"
    if is_cat:
        brand1 = "Royal Canin"
        food1 = f"{brand1} {breed_name} Adult (Best Choice)"
        if "hairless" in str(special) or "skin_care" in str(special):
            food2 = "Whiskas Skin & Coat care with salmon & tuna"
        elif energy in ("high", "very_high"):
            food2 = "Whiskas Active cat food with chicken & fish"
        else:
            food2 = "Whiskas Indoor cat food with chicken & vegetables"
        if energy in ("high", "very_high"):
            freq = "3-4 meals/day, high protein diet"
        elif size in ("large", "giant"):
            freq = "2-3 meals/day, balanced protein diet"
        else:
            freq = "2-3 meals/day, balanced diet"
    else:
        brand1 = "Royal Canin"
        size_label = {"tiny": "X-Small", "small": "Mini", "medium": "Medium", "large": "Maxi", "giant": "Giant"}
        food1 = f"{brand1} {breed_name} Adult ({size_label.get(size, 'Medium')})"
        if "obesity_prone" in special:
            food2 = "Pedigree Weight Management dry food with chicken & rice"
        elif energy in ("high", "very_high"):
            food2 = "Pedigree Active adult dry food with chicken & vegetables"
        elif size in ("giant",):
            food2 = "Pedigree Large Breed dry food with chicken & rice"
        elif size in ("tiny", "small"):
            food2 = "Pedigree Small Breed dry food with lamb & vegetables"
        else:
            food2 = "Pedigree Adult dry food with chicken & vegetables"
        if size in ("tiny", "small"):
            base_meals = "3-4 meals/day" if energy in ("high", "very_high") else "2-3 meals/day"
        elif size in ("giant",):
            base_meals = "2 meals/day"
        else:
            base_meals = "2 meals/day" if energy in ("low", "moderate") else "2-3 meals/day"
        if energy in ("high", "very_high"):
            freq = f"{base_meals}, high protein diet"
        elif "obesity_prone" in special:
            freq = f"{base_meals}, controlled calorie diet"
        else:
            freq = f"{base_meals}, balanced diet"
    return food1, food2, freq


def get_health_care(animal, size, energy, coat, special):
    is_cat = animal == "Cat"
    if any(s in special for s in ["hip_dysplasia", "heart_issues", "cancer_prone", "short_lifespan", "brachycephalic"]):
        vet = "Regular vet checkups every 3 months"
    else:
        vet = "Regular vet checkups every 6 months"

    if "dental_issues" in special:
        dental = "Dental care: Brush teeth daily, regular dental cleanings"
    else:
        dental = "Dental care: Brush teeth 2-3 times per week"

    exercise_map = {
        "cat": {"low": "15-20 minutes", "moderate": "20-30 minutes", "high": "30-45 minutes", "very_high": "45-60 minutes"},
        "dog": {
            "tiny": {"low": "15-20 minutes", "moderate": "20-30 minutes", "high": "30-45 minutes", "very_high": "45-60 minutes"},
            "small": {"low": "20-30 minutes", "moderate": "30-45 minutes", "high": "45-60 minutes", "very_high": "60-75 minutes"},
            "medium": {"low": "30-40 minutes", "moderate": "40-60 minutes", "high": "60-75 minutes", "very_high": "75-90 minutes"},
            "large": {"low": "30-45 minutes", "moderate": "45-60 minutes", "high": "60-90 minutes", "very_high": "90-120 minutes"},
            "giant": {"low": "30-45 minutes", "moderate": "45-60 minutes", "high": "60-90 minutes", "very_high": "90-120 minutes"},
        }
    }
    if is_cat:
        mins = exercise_map["cat"].get(energy, "20-30 minutes")
        exercise = f"Daily play/exercise: {mins} required"
    else:
        mins = exercise_map["dog"].get(size, {}).get(energy, "30-45 minutes")
        exercise = f"Daily exercise: {mins} required"

    grooming_map = {
        "hairless": "Skin care: Weekly bath, daily moisturizer, sunscreen for outdoors",
        "short": "Grooming: Brush coat 1-2 times weekly",
        "medium": "Grooming: Brush coat 2-3 times weekly",
        "long": "Grooming: Brush coat daily to prevent matting",
        "double": "Grooming: Brush coat 3-4 times weekly, heavy seasonal shedding",
        "curly": "Grooming: Professional grooming every 4-6 weeks, brush daily",
        "wiry": "Grooming: Hand-strip coat every 6-8 weeks, brush weekly",
    }
    grooming = grooming_map.get(coat, "Grooming: Brush coat 2-3 times weekly")
    if "skin_folds" in special:
        grooming += ", clean skin folds daily"
    if "eye_care" in special:
        grooming += ", clean eyes daily"

    return vet, dental, exercise, grooming


def get_dos(animal, temperament, energy, special):
    is_cat = animal == "Cat"
    dos = []
    if energy in ("high", "very_high"):
        dos.append("Provide plenty of mental stimulation")
    else:
        dos.append("Provide mental stimulation")
    if "social" in temperament or "friendly" in temperament:
        dos.append("Socialize early and often")
    elif "independent" in temperament:
        dos.append("Respect their independent nature")
    else:
        dos.append("Socialize early")
    dos.append("Give fresh water always")
    if is_cat:
        if "climbing" in special:
            dos.append("Provide cat trees and climbing spaces")
        elif energy in ("high", "very_high"):
            dos.append("Interactive play sessions daily")
        else:
            dos.append("Regular play time")
    else:
        if energy in ("high", "very_high"):
            dos.append("Daily vigorous exercise sessions")
        else:
            dos.append("Regular play time")
    return dos


def get_donts(animal, temperament, size, special):
    is_cat = animal == "Cat"
    donts = []
    donts.append("Avoid overfeeding")
    if is_cat:
        donts.append("No chocolate/lilies/onions")
    else:
        donts.append("No chocolate/grapes/onions")
    if "social" in temperament or "affectionate" in temperament:
        donts.append("Don't leave alone for long periods")
    elif "independent" in temperament:
        donts.append("Don't force excessive affection")
    else:
        donts.append("Don't leave alone for long periods")
    if "sensitive" in temperament or "gentle" in temperament:
        donts.append("No harsh training or punishment")
    elif "stubborn" in temperament:
        donts.append("No forceful training methods")
    else:
        donts.append("No harsh training methods")
    return donts


def get_lifestyle(animal, breed_name, size, energy, temperament, coat, special):
    is_cat = animal == "Cat"
    if is_cat:
        if energy in ("high", "very_high"):
            suited = "Active households with play space"
        elif "calm" in temperament or "quiet" in temperament:
            suited = "Quiet homes, apartments, seniors"
        else:
            suited = "Apartments or houses, most living situations"
    else:
        if size in ("giant", "large") and energy in ("high", "very_high"):
            suited = "Active families with large yards"
        elif size in ("giant", "large"):
            suited = "Family homes with yards"
        elif size in ("tiny", "small") and energy in ("low", "moderate"):
            suited = "Apartments, small homes, seniors"
        elif size in ("tiny", "small"):
            suited = "Apartments or houses with some outdoor access"
        else:
            suited = "Houses with yards, active families"

    if "temperature_sensitive" in special:
        climate = "Indoor climate-controlled environment essential"
    elif coat in ("double",):
        climate = "Cool to moderate temperatures preferred"
    elif coat in ("hairless",):
        climate = "Warm indoor environment, protect from sun and cold"
    elif coat in ("long",) and size in ("large", "giant"):
        climate = "Moderate to cool temperatures preferred"
    elif size in ("tiny", "small") and coat in ("short", "hairless"):
        climate = "Warm to moderate temperatures preferred"
    elif is_cat:
        climate = "Indoor environment recommended, moderate temperatures"
    else:
        climate = "Adapts to most climates"

    great_list = []
    if any(t in temperament for t in ["gentle", "friendly", "patient", "playful", "merry"]):
        great_list.append("Children")
    if any(t in temperament for t in ["social", "playful", "easygoing", "adaptable", "outgoing"]):
        great_list.append("other pets")
    if any(t in temperament for t in ["calm", "gentle", "quiet", "patient", "docile", "low"]):
        great_list.append("seniors")
    if any(t in temperament for t in ["loyal", "affectionate", "friendly", "devoted", "reliable", "trustworthy"]):
        great_list.append("families")
    if any(t in temperament for t in ["independent", "protective", "wild", "aloof"]) and not great_list:
        great_list = ["experienced owners"]
    elif not great_list:
        great_list = ["active individuals", "families"]
    great_with = ", ".join(dict.fromkeys(great_list))

    return suited, climate, great_with


def get_breed_info(breed_key: str) -> dict | None:
    props = BREED_PROPS.get(breed_key)
    if not props:
        return None

    size, energy, coat, temperament, special = props
    animal, breed_name = get_display_name(breed_key)

    food1, food2, freq = get_food_recommendations(animal, breed_name, size, energy, special)
    vet, dental, exercise, grooming = get_health_care(animal, size, energy, coat, special)
    dos = get_dos(animal, temperament, energy, special)
    donts = get_donts(animal, temperament, size, special)
    suited, climate, great_with = get_lifestyle(animal, breed_name, size, energy, temperament, coat, special)

    return {
        "breed_name": breed_name,
        "animal_type": animal,
        "recommended_food": {
            "best_choice": food1,
            "secondary_option": food2,
            "feeding_frequency": freq,
        },
        "health_care_tips": {
            "vet_checkup_frequency": vet,
            "dental_care": dental,
            "exercise_needs": exercise,
            "grooming_needs": grooming,
        },
        "dos": dos,
        "donts": donts,
        "lifestyle_guidance": {
            "best_suited_for": suited,
            "climate_preference": climate,
            "great_with": great_with,
        },
    }
