const maxAttempts = 20;
const timeout = 10;

async function waitForElement(selector) {
  let attempts = 0;

  while (attempts < maxAttempts) {
    const element = document.getElementById(selector);
    if (element) {
      // console.log(`Found ${selector}`);
      return element;
    }

    attempts++;
    console.warn(`Cannot find ${selector}. Trying again (${attempts + 1})`);
    await new Promise((resolve) => setTimeout(resolve, timeout));
  }

  return null;
}

async function waitForElements(selector) {
  let attempts = 0;

  while (attempts < maxAttempts) {
    const element = document.querySelectorAll(selector);
    if (element.length > 0) {
      // console.log(`Found ${selector.length} ${selector}`);
      return element;
    }

    attempts++;
    console.warn(`Cannot find all ${selector}. Trying again (${attempts + 1})`);
    await new Promise((resolve) => setTimeout(resolve, timeout));
  }

  return null;
}
