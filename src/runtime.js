export class Boundary extends HTMLElement {
    constructor() {
        super();
        this.handleClick = (event) => {
            if (!(event.target instanceof HTMLElement)) {
                console.error('Invalid target for anchor click event.');
                return;
            }
            const link = event.target.matches('a') ? event.target : event.target.closest('a');
            if (link && (!link.target || link.target === '_self')) {
                const href = link.getAttribute('href');
                if (href && this.canFetch(href)) {
                    event.preventDefault();
                    event.stopPropagation();
                    this.fetch(href, true);
                }
            }
        };
        this.handleSubmit = (event) => {
            if (!(event.target instanceof HTMLFormElement)) {
                console.error('Invalid target for submit event');
                return;
            }
            if (!(event.submitter instanceof HTMLInputElement) || !(event.submitter instanceof HTMLButtonElement)) {
                console.error('Invalid submitter for submit event');
                return;
            }
            if (this.canFetch(event.target.action) && (!event.target.target || event.target.target === '_self') && (!event.submitter.formTarget || event.submitter.formTarget === '_self')) {
                event.preventDefault();
                event.stopPropagation();
                fetch(event.target.action, {
                    method: event.target.method, body: new FormData(event.target, event.submitter), headers: {
                        Accept: "application/x.no-content"
                    }
                }).then(response => {
                    if (response.redirected) {
                        if (this.canFetch(response.url)) {
                            this.fetch(response.url, true);
                        }
                        else {
                            window.location.assign(response.url);
                        }
                    }
                    else {
                        this.fetch(window.location.href, false);
                    }
                }).catch(error => {
                    console.error(error);
                });
            }
        };
        this.addEventListener('click', this.handleClick);
        this.addEventListener('submit', this.handleSubmit);
    }
    connectedCallback() {
        if (this.hasAttribute('fetch-on-connected')) {
            this.fetch(window.location.href, false, false);
        }
    }
    removeLastPathSegment(path) {
        if (path === '/') {
            return '/';
        }
        const segments = path.split('/');
        segments.pop();
        const newPath = segments.join('/');
        if (!newPath.length) {
            return '/';
        }
        return newPath;
    }
    canFetch(url) {
        const partial = this.getAttribute('partial');
        if (!partial) {
            console.error('Missing required attribute `partial`.');
            return false;
        }
        const currentURL = new URL(window.location.href);
        const requestedURL = new URL(url, document.baseURI);
        const requestedBase = this.removeLastPathSegment(requestedURL.pathname);
        return requestedURL.host === currentURL.host && requestedBase.startsWith(partial);
    }
    loadStyle(style) {
        if (!document.head.querySelector(`link[href='${style.href}']`)) {
            const styleElement = document.createElement('link');
            styleElement.rel = 'stylesheet';
            styleElement.href = style.href;
            styleElement.media = style.media;
            document.head.appendChild(styleElement);
        }
    }
    loadScript(script) {
        if (!document.head.querySelector(`script[src='${script.src}']`)) {
            const scriptElement = document.createElement('script');
            scriptElement.src = script.src;
            if (script.module) {
                scriptElement.type = 'module';
            }
            document.head.appendChild(scriptElement);
        }
    }
    fetch(url, history, fallback = true) {
        const partial = this.getAttribute('partial');
        if (!partial) {
            console.error('Missing required attribute `partial`.');
            return false;
        }
        const requestedURL = new URL(url, document.baseURI);
        requestedURL.searchParams.set('_partial', partial);
        fetch(requestedURL)
            .then(response => response.text())
            .then(htmlString => {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = htmlString;
            const script = wrapper.querySelector('script');
            if (script) {
                const data = JSON.parse(script.innerText);
                const scriptSet = new Set(data.scripts.map(s => s.src));
                const styleSet = new Set(data.styles.map(s => s.href));
                document.head.querySelectorAll('script').forEach((script) => {
                    if (!script.src) {
                        return;
                    }
                    const url = new URL(script.src, document.baseURI);
                    if (!scriptSet.has(url.pathname)) {
                        script.remove();
                    }
                });
                data.scripts.forEach(this.loadScript);
                document.head.querySelectorAll('link').forEach((link) => {
                    if (link.rel !== 'stylesheet' || !link.href) {
                        return;
                    }
                    const url = new URL(link.href, document.baseURI);
                    if (!styleSet.has(url.pathname)) {
                        link.remove();
                    }
                });
                data.styles.forEach(this.loadStyle);
            }
            const template = wrapper.querySelector('template');
            if (template) {
                if (template.dataset.title) {
                    document.title = template.dataset.title;
                }
                this.replaceWith(template.content);
            }
            if (history) {
                window.history.pushState({ url: url }, '', url);
            }
        }).catch(error => {
            console.error(error);
            if (fallback) {
                if (history) {
                    window.location.assign(url);
                }
                else {
                    window.location.replace(url);
                }
            }
        });
    }
}
if (!customElements.get('route-layer')) {
    window.history.replaceState({ url: document.location.href }, '', document.location.href);
    window.addEventListener('popstate', event => {
        window.location.assign(event.state.url);
    });
    customElements.define('route-layer', Boundary);
}
