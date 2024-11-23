/**
 * Events for purging page cache
 *
 * @param {Event} event
 */
function catchPurgeEvent(event) {
  if (event.type === 'click') {
    event.preventDefault();
    purgePageCache();
  }

  if (event.key === 'F4') {
    purgePageCache();
  }
}

/**
 * Purge cache function
 */
function purgePageCache() {
  const apiUrl = mw.config.get('wgScriptPath') + '/api.php';
  const params = {
    action: 'purge',
    titles: mw.config.get('wgPageName'),
    format: 'json',
  };

  $.post(apiUrl, params).done(function () {
    location.reload();
  });
}

/**
 * Function to add purge button to the p-captions menu
 */
function addPurgeButton() {
  const actionsMenu = document.getElementById('p-cactions');
  if (!actionsMenu) {
    return;
  }

  const purgeListItem = document.createElement('li');
  const purgeButton = document.createElement('a');
  const purgeButtonLabel = document.createElement('span');
  const purgeButtonIcon = document.createElement('span');

  purgeListItem.id = 'ca-purge';
  purgeListItem.className = 'mw-list-item';
  purgeButton.href = '#';
  purgeButtonIcon.classList.add('tgui-icon', 'tgui-icon-eraser');
  purgeButtonLabel.textContent = mw.message('tgui-purge-button-label').text();
  purgeButtonLabel.setAttribute('title', mw.message('tgui-purge-button-title').text());

  purgeButton.addEventListener('click', catchPurgeEvent);
  document.addEventListener('keydown', catchPurgeEvent);

  purgeListItem.appendChild(purgeButton);
  purgeButton.appendChild(purgeButtonIcon);
  purgeButton.appendChild(purgeButtonLabel);

  const parentElement = actionsMenu.getElementsByTagName('ul')[0];
  parentElement.appendChild(purgeListItem);
}

module.exports = function () {
  addPurgeButton();
};
