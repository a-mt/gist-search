
/**
 * Get list of gists of the current user
 */
function get_gists() {
  var container      = document.getElementById("results"),
      offsetResponse = 0;

  if(!container) {
    return;
  }

  // Loader
  var loader = document.createElement('div');
  loader.className = "loading-spinner right";
  container.appendChild(loader);

  // Filters
  add_filters(container);

  // Toggle file list
  document.body.addEventListener('click', function(e){
    if(e.target.nodeName == "BUTTON") {
      e.preventDefault();

      // Toggle ul className "all"
      var ul = e.target.parentNode.parentNode.querySelector('ul');
      console.log(e.target.parentNode.parentNode, ul);
      if(ul.classList.contains('all')) {
        ul.classList.remove('all');
      } else {
        ul.classList.add('all');
      }
    }
  }, true);

  // List of gists
  var div = document.createElement('div');
  div.className = "inner";
  container.appendChild(div);

  /* global $ */
  var runner = $.ajax({
      url: '/index.php?get_gists=1',
      processData: false,
      xhrFields: {
          // Getting on progress streaming response
          onprogress: function(e) {
              var response = e.target.response;

              if(response.substr(response.length-1, 1) == ']') {
                try {

                  // If we receive the response too quickly, multiple parts may be together
                  var parts = response.substring(offsetResponse).split(']['),
                      n     = parts.length - 1;

                  for(var i=0; i<=n; i++) {
                    var part = (i == 0 ? '' : '[') + parts[i] + (i == n ? '' : ']'),
                        data = JSON.parse(part);

                    div.innerHTML += format_gists(data);
                  }
                  offsetResponse = response.length;
                } catch(e) {
                  console.error(e);
                }
              }
          }
      }
  });

  // Ajax done running
  runner.done(function(data) {
   console.log("done");
  });
  runner.always(function(data) {
   loader.remove();
  });
  runner.fail(function(xhr){
    if(xhr.readyState == 0 || xhr.status == 0) { // request has been canceled (change page)
      return;
    }
    console.error(xhr);
    container.innerHTML += "<p>Une erreur s'est produite</p>";
  });
}

/**
 * Format gists to be displayed
 * @param array gists
 * @return string - html
 */
function format_gists(gists) {
  var html = "";

  for(var i=0; i<gists.length; i++) {
    var gist = gists[i];

    html += `<div class="row"><a href="${gist.html_url}" target="_blank">

      <div class="about">
        ${Object.keys(gist.files).length > 1
          ? `<button class="files span">
            <i class="octicon octicon-gist"></i>${Object.keys(gist.files).length} files
          </button>`
          : `<span class="files span">
            <i class="octicon octicon-gist"></i>${Object.keys(gist.files).length} file
          </span>`
        }
        <span class="update span" title="Updated at" style="display: none">
          <i class="octicon octicon-watch"></i><time>${gist.updated_at.replace('T', ' ').replace('Z', '')}</time>
        </span>
        <span class="creation span" title="Created at">
          <i class="octicon octicon-watch"></i><time>${gist.created_at.replace('T', ' ').replace('Z', '')}</time>
        </span>
      </div>

      <label data-filter="title">${gist.description}</label>
      <span data-filter="status">${gist.public ? "" : "<i class='octicon octicon-lock' title='Secret'></i>"}</span>
      <ul>${Object.keys(gist.files).map((filename) => "<li data-filter='filename'>" + filename + "</li>").join('')}</ul>
    </a></div>`;
  }
  return html;
}

/**
 * Append the filters form to the given container
 * @param DOMElement container
 */
function add_filters(container) {
  var form = document.createElement('form');
  form.className = "filter form-control";
  form.innerHTML = `<!-- Description -->
  <div>
    <label for="title">Description</label>
    <input id="title" type="text" name="title[text]" value="">

    <div class="options">
      <input id="regex" type="checkbox" name="title[regex]" value="1">
      <label for="regex" title="Regular Expression" class="regex">Regular Expression</label>
    </div>
    <div class="options">
      <input id="match_case" type="checkbox" name="title[match_case]" value="1">
      <label for="match_case" title="Match Case" class="match_case">Match Case</label>
    </div>
    <div class="options">
      <input id="whole_words" type="checkbox" name="title[whole_words]" value="1">
      <label for="whole_words" title="Whole Words" class="whole_words">Whole Words</label>
    </div>
  </div>

  <!-- Filename -->
  <div>
    <label for="filename">Filename</label>
    <input id="filename" type="text" name="filename[text]" value="">

    <div class="options">
      <input id="f_regex" type="checkbox" name="filename[regex]" value="1">
      <label for="f_regex" title="Regular Expression" class="regex">Regular Expression</label>
    </div>
    <div class="options">
      <input id="f_match_case" type="checkbox" name="filename[match_case]" value="1">
      <label for="f_match_case" title="Match Case" class="match_case">Match Case</label>
    </div>
    <div class="options">
      <input id="f_whole_words" type="checkbox" name="filename[whole_words]" value="1">
      <label for="f_whole_words" title="Whole Words" class="whole_words">Whole Words</label>
    </div>
  </div>

  <!-- Public/secret -->
  <div>
    <label for="status">Status</label>
    <select id="status" name="status">
      <option value="">Public + secret</option>
      <option value="secret">Secret</option>
      <option value="public">Public</option>
    </select>
  </div>
  <div class="actions inline">
    <input type="submit" class="btn" value="Filter">
  </div>`;
  form.addEventListener("submit", handle_filters);
  container.appendChild(form);
}

/**
 * Handle filters form submit
 */
function handle_filters(e){
  e.preventDefault();

  // Retrieve submitted data
  var opts = {};
  for(let i=0; i<this.elements.length; i++) {
    var element = this.elements[i];

    if(element.type == "checkbox" && !element.checked) {
      continue;
    } else if(element.type == "submit" || !element.value) {
      continue;
    }
    var name = element.name.replace(/\[([^\]]+)\]/g, ".$1");
    setProperty(opts, name, element.value.trim());
  }

  // Build filters
  var filters = {};
  for(let k in opts) {
    var opt = opts[k];

    if(k == "status") {
      filters[k] = (opt == "public"
                    ? function(node) { return node.innerHTML == ""; }
                    : function(node) { return node.innerHTML != ""; });
    } else if(opt.text) {
      var text = opt.text;
      
      if(!opt.regex) {
        text = text.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
      }
      if(opt.whole_words) {
        text = '\\b' + text + '\\b';
      }
      let re = new RegExp(text, opt.match_case ? "" : "i");

      filters[k] = function(node) {
        return node.innerHTML.match(re);
      };
    }
  }

  // Filters rows
  var container = this.nextElementSibling;
  for(let i=0; i<container.children.length; i++) {
    var child = container.children[i],
        b     = true;

    for(let k in filters) {
      b = Array.from(child.querySelectorAll('[data-filter="' + k + '"]')).some(filters[k]);
      if(!b) {
        break;
      }
    }
    if(b) {
      child.classList.remove("hide");
    } else {
      child.classList.add("hide");
    }
  }
}

/**
 * Recursively set a property that contains dots
 * @param object obj
 * @param string propertyName
 * @param mixed value
 */
function setProperty(obj, propertyName, value) {
  var parts = propertyName.split( "." ),
      prop  = parts.shift();

  if(parts.length == 0) {
    obj[prop] = value;
  } else {
    if(typeof obj[prop] == "undefined") {
      obj[prop] = {};
    }
    setProperty(obj[prop], parts.join("."), value);
  }
}

window.onload = get_gists;