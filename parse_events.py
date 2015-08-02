#!/usr/bin/env python
import xlrd
import json
import calendar
# import time


def get_values(row):
    return map(lambda x: x.value, row)


def parse_workbook(filename):
    out = {}
    book = xlrd.open_workbook(filename)
    sh = book.sheet_by_index(0)
    header = get_values(sh.row(0))
    for rx in range(1, sh.nrows):
        row = dict(zip(header, get_values(sh.row(rx))))
        row['id'] = int(row['id'])
        dt_xls = xlrd.xldate_as_tuple(row['datetime'], 0)
        dt = calendar.timegm(dt_xls)
        row['datetime'] = dt
        if row['id'] == 417:
            print dt
            assert dt == 1412281800
        del row['description']
        out[row['id']] = row
    return out

if __name__ == '__main__':
    data = parse_workbook('New CAAS-Events.xls')
    with open('updated_events.json', 'wb') as f:
        json.dump(data, f, indent=4)
