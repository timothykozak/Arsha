<?php

// MySearch.php
//
// This code is used to look through server side html files
// for a search term.  It looks through all content in the body
// and ignores scripts.  All html tags and their attributes
// are stripped out but all of the content remains.  This means
// that even the text of a button will be part of the search.
// It will return a list of files that contain the search term.
// The template will be used to give information about each
// file.
//
// This template with a search for Quail...
//      `<h5 class="search-title"><a target="_top" href="#{href}" class="search-link">#{title}</a></h5>
//       <p>...#{token}...</p>
//       <p class="match"><em>Terms matched: #{count} - URL: #{href}</em></p>`
//
// ...will generate this result.
//       <div class="search-results">
//         <ol class="search-list">
//           <li class="search-list-item">
//             <h5 class="search-title"><a target="_top" href="../MySearch/QTest.html" class="search-link">Words With Q</a></h5>
//             <p>... Words with Q <span class="search">Quail</span> Quartz Quaker ...</p>
//             <p class="match"><em>Terms matched: 1 - URL: ../MySearch/QTest.html</em></p>
//           </li>
//         </ol>
//       </div>
//
// The required parameters passed are:
//      s - the text to be searched for
//      template - a string of text that will be updated
//          with information found during the search.
//          #{href} is replaced with the file location
//          #{title} is replaced with the title of the html page
//          #{token} is replaced with the first string of
//                   text that contains the found search term
//          #{count} is replaced with the number of times
//                   that the search term was found in the file
// Optional parameters are:
//      search_dir - the directory in which to begin the search,
//          please note that this is relative to the location
//          of this file.  Subdirectories are searched if
//          $recursive is true.
//      filter - file types to search, typically *.html
//
//  In the returned html the following classes are automatically
//  inserted without being defined:
//      MyCC-search
//      MyCC-search-results
//      MyCC-search-quick-result
//      MyCC-search-list-item
//      MyCC-search-list-item-all
//      MyCC-search-error
//
// This code was originally part of a download of the Charia template
// from monsterone.com.

if (!isset($_GET['s'])) {
    die('You must define a search term!');
}

$search_in = array('html', 'htm');  // Allowable filetypes to search in
$search_dir = '../../../../';  // Starting directory, might be overridden by a passed parameter
$recursive = true;  // Search subdirectories
define('SIDE_CHARS', 15);
$file_count = 0;    // The number of files found
$search_term = mb_strtolower($_GET['s'], 'UTF-8');

if ($search_term == "?s=") {
    $search_term = "";
}

if (isset($_GET['search_dir'])) {
    $search_dir = $_GET['search_dir'];
}

$search_term = preg_replace('/^\/$/', '"/"', $search_term);
$search_term = preg_replace('/\+/', ' ', $search_term);
$search_term_length = strlen($search_term);

$final_result = array();

$search_filter_init = $_GET['filter'];
$search_filter = preg_replace("/\*/", ".*", $search_filter_init);

$search_template = preg_replace('/\+/', ' ', $_GET['template']);
preg_match_all("/\#\{((?!title|href|token|count)[a-z]*)\}/", $search_template, $template_tokens);
$template_tokens = $template_tokens[1];

$files = list_files($search_dir);

foreach ($files as $file) {

    if (0 == filesize($file)) {
        continue;
    }

    if (!preg_match("/" . $search_filter . "/", $file)) {
        continue;
    }

    $contents = file_get_contents($file);
    preg_match("/<title>(.*)<\/title>/", $contents, $page_title); //getting page title
    if (preg_match("/<body.*>(.*)<\/body>/si", $contents, $body_content)) { //getting content only between <body></body> tags
        $body_content = preg_replace("/<script.*>.*<\/script>/si", '', $body_content);  // Remove any scripts
        $clean_content = strip_tags($body_content[0]); //remove html tags
        $clean_content = preg_replace('/\s+/', ' ', $clean_content); //remove duplicate whitespaces, carriage returns, tabs, etc

        $found = strpos_recursive(mb_strtolower($clean_content, 'UTF-8'), $search_term);

        $final_result[$file_count]['page_title'][] = $page_title[1];
        $cleanFile = preg_replace("/\.\.\//", "", $file);
        $final_result[$file_count]['file_name'][] = $cleanFile;
    }

    for ($j = 0; $j < count($template_tokens); $j++) {
        if (preg_match("/\<meta\s+name=[\'|\"]" . $template_tokens[$j] . "[\'|\"]\s+content=[\'|\"](.*)[\'|\"]\>/", $contents, $res)) {
            $final_result[$file_count][$template_tokens[$j]] = $res[1];
        }
    }

    if ($found && !empty($found)) {
        for ($z = 0; $z < count($found[0]); $z++) {
            $pos = $found[0][$z][1];
            $side_chars = SIDE_CHARS;
            if ($pos < SIDE_CHARS) {
                $side_chars = $pos;
                $pos_end = SIDE_CHARS * 9 + $search_term_length;
            } else {
                $pos_end = SIDE_CHARS * 9 + $search_term_length;
            }

            $pos_start = $pos - $side_chars;
            $str = substr($clean_content, $pos_start, $pos_end);
            $result = preg_replace('/' . $search_term . '/ui', '<span class="MyCC-search">\0</span>', $str);
            $final_result[$file_count]['search_result'][] = $result;

        }
    } else {
        $final_result[$file_count]['search_result'][] = '';
    }
    $file_count++;
}

if ($file_count > 0) {

//Sort final result
    foreach ($final_result as $key => $row) {
        $search_result[$key] = $row['search_result'];
    }
    array_multisort($search_result, SORT_DESC, $final_result);
}

?>

<div class="search-results">
    <ol class="search-list">
        <?php
        $sum_of_results = 0;
        $match_count = 0;
        for ($i = 0; $i < count($final_result); $i++) {
            if (!empty($final_result[$i]['search_result'][0]) || $final_result[$i]['search_result'][0] !== '') {
                $match_count++;
                $sum_of_results += count($final_result[$i]['search_result']);
                    ?>
                    <li class="search-list-item">

                        <?php
                        $replacement = [$final_result[$i]['page_title'][0],
                            $final_result[$i]['file_name'][0],
                            $final_result[$i]['search_result'][0],
                            count($final_result[$i]['search_result'])
                        ];
                        $template = preg_replace(["/#{title}/","/#{href}/","/#{token}/","/#{count}/"],$replacement, $search_template);
                        for ($k = 0; $k < count($template_tokens); $k++){
                            if (isset($final_result[$i][$template_tokens[$k]])){
                                $template = preg_replace("/#{" . $template_tokens[$k] . "}/", $final_result[$i][$template_tokens[$k]], $template);
                            }else{
                                $template = preg_replace("/#{" . $template_tokens[$k] . "}/", " ", $template);
                            }
                        }

                        echo $template; ?>
                    </li>
                    <?php
            }
        }

        if ($match_count == 0) {
            echo '<li><div class="MyCC-search-error">No results found for "<span class="MyCC-search">' . $search_term . '</span>"<div/></li>';
        }
        ?>
    </ol>
</div>

<?php
//lists all the files in the directory given (and sub-directories if it is enabled)
function list_files($dir)
{
    global $recursive, $search_in;

    $result = array();
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if (!($file == '.' || $file == '..')) {
                    $file = $dir . '/' . $file;
                    if (is_dir($file) && $recursive == true && $file != './.' && $file != './..') {
                        $result = array_merge($result, list_files($file));
                    } else if (!is_dir($file)) {
                        if (in_array(get_file_extension($file), $search_in)) {
                            $result[] = $file;
                        }
                    }
                }
            }
        }
    }
    return $result;
}

//returns the extension of a file
function get_file_extension($filename)
{
    $result = '';
    $parts = explode('.', $filename);
    if (is_array($parts) && count($parts) > 1) {
        $result = end($parts);
    }
    return $result;
}

function strpos_recursive($haystack, $needle, $offset = 0, &$results = array())
{
    $offset = stripos($haystack, $needle, $offset);
    if ($offset === false) {
        return $results;
    } else {
        $pattern = '/' . $needle . '/ui';
        preg_match_all($pattern, $haystack, $results, PREG_OFFSET_CAPTURE);
        return $results;
    }
}

?>
