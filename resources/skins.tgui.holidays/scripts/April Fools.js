setInterval(() => {
  let hue = (parseInt(getComputedStyle(document.documentElement).getPropertyValue('--primary-hue')) || 0) + 10;
  document.documentElement.style.setProperty('--primary-hue', hue % 360);
}, 200);

function waitForButton() {
  const observer = new MutationObserver(() => {
    const container = document.getElementById('skin-client-prefs-tgui-feature-holidays');
    if (!container) return;

    const buttons = container.querySelectorAll('.tgui-client-prefs-radio__label');
    if (buttons.length < 2) return;

    const button = buttons[1];
    observer.disconnect();
    button.addEventListener(
      'click',
      () => {
        new Audio('/skins/TGUI/resources/skins.tgui.holidays/assets/HONK.mp3').play();
      },
      { once: true },
    );
  });

  document.addEventListener('DOMContentLoaded', () => {
    observer.observe(document.body, { childList: true, subtree: true });
  });
}

waitForButton();
