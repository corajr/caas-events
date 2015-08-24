#!/usr/bin/env python3

import csv
import json
import re
from collections import Counter
from nameparser import HumanName


def unspace(text):
    return re.sub(r'\s+', ' ', text).strip()


def parse_course_numbers(text):
    text = unspace(text)
    numbers = re.findall(r'[A-Z]{3,3}\s+\d{3,3}', text)
    return numbers


def find_aas(lst):
    for x in lst:
        if x.startswith('AAS'):
            return x.partition(' ')[2]


def get_time_from(row, suffix, name):
    if 'Section ' + suffix not in row:
        return None
    info = []
    info.append('{} {}:'.format(name, unspace(row['Section ' + suffix])))
    info.append(unspace(row['Time ' + suffix]))
    info.append(unspace(row['Days ' + suffix]))
    return ' '.join(info)


def get_courses(course_csv):
    courses = []
    with open(course_csv) as f:
        reader = csv.DictReader(f)
        for row in reader:
            row = {k: v.strip() for k, v in row.items()}
            if "" in row:
                del row[""]
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
            if row['slug'] is None:
                continue
            if int(row['slug']) >= 500:
                row['Program'] = 'Graduate'
            else:
                row['Program'] = 'Undergraduate'
            if course_csv == 'Department Courses.csv':
                row['Lecture'] = get_time_from(row, '1', 'Lecture')
                row['Precept'] = get_time_from(row, '2', 'Precept')
                row['Semester'] = 'Fall 2015'
            courses.append(row)
    return courses


def merge_courses(old_course, new_course):
    for k in old_course.keys():
        if new_course.get(k) in ['', []]:
            new_course[k] = old_course[k]
    return new_course


def get_course_info(courses):
    course_info = {}
    for course in courses:
        course_id = course['slug']
        if course_id in course_info:
            old_course = course_info[course_id]
            course_info[course_id] = merge_courses(old_course, course)
        else:
            course_info[course_id] = course
    return course_info


def evaluate_courses(course_info):
    """Check to see that courses have all fields filled"""
    c = Counter()
    for course in course_info.values():
        for k, v in course.items():
            if v not in ['', []]:
                c[k] += 1
    for k in sorted(c.keys()):
        v = c[k]
        print("{}:\t{:.0%}".format(k, float(v)/len(course_info)))

if __name__ == '__main__':
    courses = get_courses('courses.csv')
    courses.extend(get_courses('Department Courses.csv'))
    print(len(courses))
    course_info = get_course_info(courses)
    evaluate_courses(course_info)
    with open('scripts/courses.json', 'w') as f:
        json.dump(course_info, f, indent=4)
