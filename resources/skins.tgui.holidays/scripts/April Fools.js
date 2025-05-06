setInterval(() => {
  let hue = (parseInt(getComputedStyle(document.documentElement).getPropertyValue('--primary-hue')) || 0) + 10;
  document.documentElement.style.setProperty('--primary-hue', hue % 360);
}, 200);
