<?php

/*
	Extension:Moderation - MediaWiki extension.
	Copyright (C) 2015 Edward Chernenko.

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
*/

/**
	@file
	@brief Verifies that moderation tokens are required.
*/

require_once(__DIR__ . "/../ModerationTestsuite.php");


class ModerationTestTokens extends MediaWikiTestCase
{
	public function testTokens() {
		$t = new ModerationTestsuite();

		$t->fetchSpecial();
		$t->loginAs($t->unprivilegedUser);
		$t->doTestEdit();
		$t->fetchSpecialAndDiff();

		$entry = $t->new_entries[0];

		$links = array($entry->approveLink,
			$entry->approveAllLink,
			$entry->rejectLink,
			$entry->rejectAllLink
		);
		foreach($links as $url)
		{
			$url .= '&uselang=qqx'; # Show message IDs instead of text

			$bad_url = preg_replace('/token=[^&]*/', '', $url);
			$title = $t->getHtmlTitleByURL($bad_url);
			$this->assertRegExp('/\(sessionfailure-title\)/', $title);

			/* Double-check that nothing happened */
			$t->fetchSpecialAndDiff();
			$this->assertCount(0, $t->new_entries);
			$this->assertCount(0, $t->deleted_entries);

			# Would the wrong token work?

			$bad_url = preg_replace('/(token=)([^&]*)/', '\1WRONG\2', $url);
			$title = $t->getHtmlTitleByURL($bad_url);
			$this->assertRegExp('/\(sessionfailure-title\)/', $title);

			/* Double-check that nothing happened */
			$t->fetchSpecialAndDiff();
			$this->assertCount(0, $t->new_entries);
			$this->assertCount(0, $t->deleted_entries);
		}

		$links = array($entry->showLink);
		foreach($links as $url)
		{
			$url .= '&uselang=qqx'; # Show message IDs instead of text

			$bad_url = preg_replace('/token=[^&]*/', '', $url);
			$title = $t->getHtmlTitleByURL($bad_url);
			$this->assertNotRegExp('/\(sessionfailure-title\)/', $title);
		}
	}
}
