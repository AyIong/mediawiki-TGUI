const timeout = 2000; // 2 Seconds

async function waitForElement(selector) {
  return new Promise((resolve, reject) => {
    const element = document.getElementById(selector);
    if (element) {
      return resolve(element);
    }

    const observer = new MutationObserver(() => {
      const element = document.getElementById(selector);
      if (element) {
        observer.disconnect();
        clearTimeout(timer);
        resolve(element);
      }
    });

    observer.observe(document.body, { childList: true, subtree: true });

    const timer = setTimeout(() => {
      observer.disconnect();
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
      const elements = element.querySelectorAll(selector);
      if (elements.length > 0) {
        observer.disconnect();
        clearTimeout(timer);
        resolve(elements);
      }
    });

    observer.observe(document.body, { childList: true, subtree: true });

    const timer = setTimeout(() => {
      observer.disconnect();
      reject(new Error(`Elements ${selector} not found within timeout`));
    }, timeout);
  });
}
