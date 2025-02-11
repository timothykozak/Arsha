// MySearch.js
//
// This works in conjunction with with MySearch.php
// to look through the html files on the server for
// the searchString somewhere in the body of the page.
// It passes a template that is updated with information
// for each file found.  Otherwise it returns No Search
// Results.
//
// Please note that the searchDir is relative to the
// location of MySearch.php.

function getResult() {

  let xhr = new XMLHttpRequest();
  let resultsElement = document.getElementById("search_results")
  let searchElement = document.getElementById("search_input");
  let searchString = searchElement.value;
  let request = new URLSearchParams({s: searchString});
  let handler = './assets/vendor/MyCCs/MySearch.php';
  let filter = '*.html';
  let searchDir = '../../..';   // Need to go up three directories to get to the root
                                      // because of the location of MySearch.php
  let template = `<h5 class="MyCC-search-title"><a target="_top" href="#{href}" class="search-link">#{title}</a></h5>
                        <p>...#{token}...</p>
                        <p class="MyCC-match"><em>Terms matched: #{count} - URL: #{href}</em></p>`;


  request.append( 'template', template );
  request.append( 'filter', filter );
  request.append( 'search_dir', searchDir );

  xhr.open( 'GET', handler +'?'+ request.toString(), true );

  xhr.onreadystatechange = ( event ) => {
    if( xhr.readyState === 4 && xhr.status === 200 ) {
      resultsElement.innerHTML = xhr.response;
    }
  };

  xhr.send();
}
