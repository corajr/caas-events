import nltk
from nameparser.parser import HumanName
from pprint import pprint

def get_human_names(text):
    tokens = nltk.tokenize.word_tokenize(text)
    pos = nltk.pos_tag(tokens)
    sentt = nltk.ne_chunk(pos, binary = False)
    person_list = []
    person = []
    name = ""
    for subtree in sentt.subtrees(filter=lambda t: t.label() == 'PERSON'):
        for leaf in subtree.leaves():
            person.append(leaf[0])
        if len(person) > 1: #avoid grabbing lone surnames
            for part in person:
                name += part + ' '
            if name[:-1] not in person_list:
                person_list.append(name[:-1])
            name = ''
        person = []

    return [HumanName(x) for x in person_list]

known_false = ["Dionne Worthy",
               "Please RSVP",
               "University",
               "College",
               "School",
               "Students",
               "Santa Barbara",
               "Chair",
               "Books",
               "Graduate",
               "Faculty",
               "Fellowships",
               "Show",
               "International",
               "Scholarship",
               "Du Bois",
               "Curator",
               "Lecture",
               "Music",
               "Center",
               "Chika Okeke",
               "Princeton",
               "Speaker",
               "Seminar",
               "Galler",
               "Academic",
               "Study",
               "Studies",
               "Panelist",
               "Professor",
               "Parking",
               "Open House",
               ]

def name_heuristic(name):
    for x in known_false:
        if x in name:
            return False
    return True

def get_names(title_text, text):
    title_names = get_human_names(title_text)
    text_names = get_human_names(text)
    names_out = set(name.full_name for name in title_names + text_names)
    names_out = filter(name_heuristic, names_out)[:4]
    return ', '.join(names_out)
