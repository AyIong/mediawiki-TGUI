function init(bodyContent) {
  const elements = bodyContent.querySelectorAll('.tgui-clipboard');
  if (!elements.length) {
    return;
  }

  for (const element of elements) {
    let contentToCopy = element;

    // Just add button directly to element if it has content class
    if (element.classList.contains('tgui-clipboard-content')) {
      const copyButton = createCopyButton(contentToCopy);
      element.insertBefore(copyButton, element.firstChild);
      continue;
    }

    // Otherwise, try to find content
    contentToCopy = element.querySelector('.tgui-clipboard-content');
    if (!contentToCopy) {
      console.error('[TGUI] found container with tgui-clipboard but without tgui-clipboard-content!', element);
      continue;
    }

    const copyButton = createCopyButton(contentToCopy);
    if (element.classList.contains('mw-collapsible')) {
      const toggle = element.querySelector('.mw-collapsible-toggle');
      if (toggle) {
        toggle.insertAdjacentElement('afterend', copyButton);
        continue;
      }
    }
    element.insertBefore(copyButton, element.firstChild);
  }
}

function createCopyButton(content) {
  if (!content) {
    return;
  }

  const copyButton = document.createElement('button');
  copyButton.classList.add('tgui-clipboard-button');
  copyButton.setAttribute('title', mw.message('tgui-clipboard-tooltip').text());
  copyButton.addEventListener('click', () => copyToClipboard(content, copyButton));
  return copyButton;
}

function copyToClipboard(content, button) {
  const hideTimeout = 5000;
  const textToCopy = content.textContent.trim();
  navigator.clipboard
    .writeText(textToCopy)
    .then(() => {
      mw.notify(mw.message('tgui-clipboard-success').text(), {
        type: 'success',
        autoHideSeconds: hideTimeout,
      });
      button.classList.add('copied');
      setTimeout(() => {
        button.classList.remove('copied');
      }, hideTimeout);
    })
    .catch((err) => {
      console.error('[TGUI] Copy error:', err);
      mw.notify(mw.message('tgui-clipboard-failure').text(), {
        type: 'error',
        autoHideSeconds: hideTimeout,
      });
    });
}

module.exports = {
  init: init,
};
