#!/usr/bin/env python3

import csv

with open('courses.csv') as f:
    reader = csv.DictReader(f)
    for row in reader:
        print(row)
