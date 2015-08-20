#!/usr/bin/env python3

import csv
import json
import re
from nameparser import HumanName


def parse_course_numbers(text):
    text = re.sub(r'\s+', ' ', text)
    numbers = re.findall(r'[A-Z]{3,3}\s+\d{3,3}', text)
    return numbers

def find_aas(lst):
    for x in lst:
        if x.startswith('AAS'):
            return x.partition(' ')[2]

def get_courses(course_csv):
    courses = []
    with open(course_csv) as f:
        reader = csv.DictReader(f)
        for row in reader:
            row = {k: v.strip() for k, v in row.items()}
            instructor = HumanName(row.pop('Instructor'))
            if instructor.last != "":
                row['Instructors'] = [{
                    'firstName': instructor.first,
                    'lastName': instructor.last
                }]
            else:
                row['Instructors'] = []
            number = row.pop('Course #')
            row['courseNumber'] = parse_course_numbers(number)
            row['slug'] = find_aas(row['courseNumber'])
            courses.append(row)
    return courses

if __name__ == '__main__':
    courses = get_courses('courses.csv')
    print(len(courses))
    course_info = {}
    for course in courses:
        course_info[course['courseNumber'][0]] = course
    with open('courses.json', 'w') as f:
        json.dump(course_info, f, indent=4)
