#!/usr/bin/env python3

import csv
import json


def nameToAuthor(names, lastName):
    if lastName in names:
        author = names[lastName]
        return {'family': lastName, 'given': author['firstName']}
    return {'family': lastName}


def get_content(fname='Public Scholarship - Sheet1.csv'):
    content = []
    with open('names.json') as names_f:
        names = json.load(names_f)
    with open(fname) as f:
        reader = csv.DictReader(f)
        for i, row in enumerate(reader):
            item_id = ('ps' + row['S.No']
                       if row['S.No'] != ''
                       else 'ps' + str(i+1))
            category = row['Category']
            item_type = 'webpage'
            if category == 'Review':
                item_type = 'review'
            elif category == 'Public Essay':
                item_type = 'article'
            item = {
                'id': item_id,
                'title': row['Content Name:'],
                'author': [nameToAuthor(names, row['Last Name:'])],
                'URL': row['Link:'],
                'type': item_type,
                'note': category
            }
            content.append(item)
    return content


def main():
    content = get_content()
    with open('content.json', 'w') as f:
        json.dump(content, f, indent=4)

if __name__ == '__main__':
    main()
