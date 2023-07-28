<?php
class TaglishCleaner
{
    // Function to normalize the input text (lowercase, remove special characters)
    public function normalizeText($input)
    {
        // Convert the text to lowercase
        $input = mb_strtolower($input, 'UTF-8');

        // Remove special characters and symbols
        $input = preg_replace('/[^\p{L}\p{N}\s]/u', '', $input);

        return $input;
    }

    // Function to detect the language of the input text (Tagalog or English)
    public function detectLanguage($input)
    {
        // Implement language detection logic here
        // You can use external libraries or machine learning models for this purpose
        // For simplicity, we'll assume English if the input contains alphabetic characters (English characters)
        return preg_match('/[A-Za-z]/', $input) ? 'english' : 'tagalog';
    }

    // Function to remove stop words from the input text
    public function removeStopWords($input)
    {
        // Stop words lists for both Tagalog and English
        $stopWords = array('ang', 'ng', 'sa', 'at', 'si', 'ni','the', 'and', 'in', 'at', 'is', 'for');

        // Remove stop words from the input text
        $words = explode(' ', $input);

    var_dump( $words);

        $filteredWords = array_diff($words, $stopWords);

        var_dump( $filteredWords);

        $filteredText = implode(' ', $filteredWords);

        return $filteredText;
    }

    // Function to clean up the input text
    public function cleanUpTaglish($input)
    {
        $normalizedText = $this->normalizeText($input);

        $cleanedText = $this->removeStopWords($normalizedText);

        return $cleanedText;
    }
}

// Test the TaglishCleaner class
$taglishInput = "Ano ang weather sa Manila today?";
$cleaner = new TaglishCleaner();
$cleanedText = $cleaner->cleanUpTaglish($taglishInput);
echo "Cleaned Text: " . $cleanedText;
