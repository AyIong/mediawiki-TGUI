const timeout = 2000; // 2 Seconds

async function waitForElement(selector, optional = false) {
  return new Promise((resolve, reject) => {
    const element = document.getElementById(selector);
    if (element) {
      return resolve(element);
    }

    const observer = new MutationObserver(() => {
      const foundElement = document.getElementById(selector);
      if (foundElement) {
        observer.disconnect();
        clearTimeout(timer);
        resolve(foundElement);
      }
    });

    observer.observe(document, { childList: true, subtree: true });

    const timer = setTimeout(() => {
      observer.disconnect();
      if (optional) {
        resolve(null);
        return;
      }

      reject(new Error(`Element ${selector} not found within timeout`));
    }, timeout);
  });
}

async function waitForElements(selector, element = document) {
  return new Promise((resolve, reject) => {
    const elements = element.querySelectorAll(selector);
    if (elements.length > 0) {
      return resolve(elements);
    }

    const observer = new MutationObserver(() => {
      const foundElements = element.querySelectorAll(selector);
      if (foundElements.length > 0) {
        observer.disconnect();
        clearTimeout(timer);
        resolve(foundElements);
      }
    });

    observer.observe(document, { childList: true, subtree: true });

    const timer = setTimeout(() => {
      observer.disconnect();
      reject(new Error(`Elements ${selector} not found within timeout`));
    }, timeout);
  });
}
