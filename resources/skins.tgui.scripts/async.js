async function waitForElement(selector) {
  while (document.getElementById(selector) === null) {
    await new Promise((resolve) => requestAnimationFrame(resolve));
  }
  return document.getElementById(selector);
}

async function waitForElements(selector) {
  while (document.querySelectorAll(selector).length === 0) {
    await new Promise((resolve) => requestAnimationFrame(resolve));
  }
  return document.querySelectorAll(selector);
}
