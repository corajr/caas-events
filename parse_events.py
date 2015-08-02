#!/usr/bin/env python
import xlrd
import json
import pytz
import datetime
import calendar
from nameparser import HumanName


eastern = pytz.timezone('US/Eastern')


def get_values(row):
    return map(lambda x: x.value, row)


def get_authors(row):
    names = row['presenters'].split(u',')
    authors = [HumanName(x) for x in names] if len(names) > 0 and names[0] != '' else []
    return [{'firstName': x.first, 'lastName': x.last} for x in authors]


def parse_workbook(filename):
    out = {}
    book = xlrd.open_workbook(filename)
    sh = book.sheet_by_index(0)
    header = get_values(sh.row(0))
    for rx in range(1, sh.nrows):
        row = dict(zip(header, get_values(sh.row(rx))))
        row['id'] = int(row['id'])
        dt_xls = xlrd.xldate_as_tuple(row['datetime'], 0)
        dt = datetime.datetime(*dt_xls)
        dt = eastern.localize(dt)
        dt = calendar.timegm(dt.utctimetuple())
        row['datetime'] = dt
        row['presenters'] = get_authors(row)
        del row['description']
        out[row['id']] = row
    return out

if __name__ == '__main__':
    data = parse_workbook('New CAAS-Events.xls')
    with open('updated_events.json', 'wb') as f:
        json.dump(data.values(), f, indent=4)
