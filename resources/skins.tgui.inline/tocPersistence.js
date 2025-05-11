const TOC_COLLAPSED_CLASS = 'tgui-toc-collapsed';
const isCollapsed = localStorage.getItem('TGUI-ToC-Collapsed');
const tocID = 'tgui-toc';

async function waitToC() {
  try {
    const tocReady = await waitForElement(tocID, true);
    if (tocReady) {
      restoreTOCState();
      handleWindowSize();
      window.addEventListener('resize', handleWindowSize);
    }
  } catch (error) {
    console.error('Oh nooo... ToC not found', error);
  }
}

function restoreTOCState() {
  if (isCollapsed === 'true') {
    document.body.classList.add(TOC_COLLAPSED_CLASS);
  }
}

function handleWindowSize() {
  if (window.innerWidth < 999) {
    document.body.classList.add(TOC_COLLAPSED_CLASS);
  } else {
    document.body.classList.remove(TOC_COLLAPSED_CLASS);
  }
}

// Move ToC for mobile devices
function moveElement() {
  const toc = document.getElementById('tgui-toc');
  const newContainer = document.getElementById('bodyContent');
  const mediaQuery = window.matchMedia('(max-width: 719px)');

  function handleMediaChange(e) {
    if (!toc) {
      return;
    }

    if (e.matches) {
      newContainer.appendChild(toc);
    } else {
      document.querySelector('.tgui-sidebar-container').appendChild(toc);
    }
  }

  handleMediaChange(mediaQuery);
  mediaQuery.addEventListener('change', handleMediaChange);
}

document.addEventListener('DOMContentLoaded', () => {
  waitToC();
  moveElement();
});
