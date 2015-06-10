#!/usr/bin/env python

from parse import *
from datetime import datetime
import urlparse
import unittest

kelley_desc = """The last in the series of three lectures.

Sponsored jointly by the Center for African American Studies and Princeton University Press, the Toni Morrison Lectures will be held annually and spotlight the new and exciting work of scholars and writers who have risen to positions of prominence both in academe and in the broader world of letters.

The lectures will be published in book form by Princeton University Press and celebrate the expansive literary imagination, intellectual adventurousness and political insightfulness that characterize the writing of Toni Morrison.

Robin D. G. Kelley, Distinguished Professor of History & Gary B. Nash Endowed Chair in United States History."""

class TestEventScrape(unittest.TestCase):
    def test_get_years(self):
        years = scrape_all_years(archive_url, dry_run=True)
        self.assertEqual(len(years), 10)

    def test_get_events_from_page(self):
        current_year_url = urlparse.urljoin(base_url, "index.xml?displayyear=2015")
        events = scrape_index(current_year_url, dry_run=True)
        self.assertEqual(len(events), 48)

    def test_parse_event(self):
        event_url = urlparse.urljoin(base_url, "viewevent.xml?id=477")
        scrape_event(event_url)

        events = scraperwiki.sql.select("* FROM swdata WHERE id=477")
        self.assertEqual(len(events), 1)

        event = events[0]
        self.assertEqual(event['id'], 477)
        self.assertEqual(event['title'], 'Toni Morrison Lectures by Robin D. G. Kelley - "Ending War?: Decolonial Democracy Against Neoliberalism"')
        self.assertEqual(event['location'], "McCosh Hall 10")
        self.assertEqual(retrieve_date(event['datetime']), datetime(2015, 4, 15, 17, 30))
        self.assertEqual(event['description'], kelley_desc)
        self.assertIn("/africanamericanstudies/events/detail/images/Toni_Morrison-2015-04-15.jpg", event['images'])
