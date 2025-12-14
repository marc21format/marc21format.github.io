<?php

namespace App\Http\Livewire\Traits;

trait HandlesTitleAbbreviation
{
	/**
	 * Compute an abbreviation from a string.
	 * - skips common stop words
	 * - maps highschool variants to HS
	 */
	protected function computeAbbr($s)
	{
		if (! $s || ! trim($s)) return '';

		$stopWords = ['the','and','of','in','a','an','for','to','on','at','by','with','vs','or'];

		// Split on non-alphanumeric (unicode-aware)
		$words = preg_split('/[^\p{L}\p{N}]+/u', $s);

		$tokens = [];

		foreach ($words as $w) {
			if (! $w) continue;
			$lw = mb_strtolower($w);
			if (in_array($lw, $stopWords)) continue;
			$cleaned = preg_replace('/[^\p{L}\p{N}]/u', '', $w);
			if (! $cleaned) continue;
			if (in_array($lw, ['highschool', 'high-school', 'high_school'])) { $tokens[] = 'HS'; continue; }
			$tokens[] = mb_strtoupper(mb_substr($cleaned, 0, 1));
		}

		return implode('', $tokens);
	}

	/**
	 * Title-case a string: uppercase first letter of each significant word,
	 * keep stop words (articles, conjunctions, prepositions) lowercase except
	 * when they are the first word.
	 */
	protected function titleCase($s)
	{
		if (! $s || ! trim($s)) return '';

		$stopWords = ['the','and','of','in','a','an','for','to','on','at','by','with','vs','or'];

		$s = trim($s);

		// Split into words while preserving internal whitespace
		$words = preg_split('/(\s+)/u', $s, -1, PREG_SPLIT_DELIM_CAPTURE);

		$result = '';
		$wordCount = 0;

		foreach ($words as $part) {
			// if this part is whitespace, append as-is
			if (preg_match('/^\s+$/u', $part)) {
				$result .= $part;
				continue;
			}

			// This is a word token
			$wordCount++;
			$lw = mb_strtolower($part);

			if ($wordCount === 1) {
				// always capitalize first word
				$result .= mb_strtoupper(mb_substr($part, 0, 1)) . mb_strtolower(mb_substr($part, 1));
				continue;
			}

			if (in_array($lw, $stopWords)) {
				$result .= $lw;
				continue;
			}

			$result .= mb_strtoupper(mb_substr($part, 0, 1)) . mb_strtolower(mb_substr($part, 1));
		}

		return $result;
	}
}
