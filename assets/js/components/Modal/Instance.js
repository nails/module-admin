class Instance {
    /**
     * Construct Instance
     * @param {_ADMIN_PROXY} adminController
     * @param {object} options
     */
    constructor(adminController, options = {}) {

        this.adminController = adminController;
        options = options || {};

        this.classes = {
            container: 'modal',
            inner: 'modal__inner',
            close: 'modal__close',
            title: 'modal__title',
            body: 'modal__body',
            show: 'modal--open',
            processed: 'modal--processed',
        };

        if (options.el) {

            this.container = options.el;
            this.inner = options.el.querySelector('.modal__inner');
            this.close = options.el.querySelector('.modal__close');
            this.title = options.el.querySelector('.modal__title');
            this.body = options.el.querySelector('.modal__body');

        } else {

            let defaultTitle = options.defaultTitle || null;
            let defaultBody = options.defaultBody || null;
            let minWidth = options.minWidth || null;
            let maxWidth = options.maxWidth || null;

            this.container = this.newDiv('container');
            this.inner = this.newDiv('inner');
            this.close = this.newDiv('close', '&times;');
            this.title = this.newDiv('title');
            this.body = this.newDiv('body');

            this.addClass(this.container, 'processed')

            //  Set styles
            if (minWidth) {
                this.addStyle(this.inner, 'minWidth', minWidth);
            }
            if (maxWidth) {
                this.addStyle(this.inner, 'maxWidth', maxWidth);
            }

            //  Set content
            this
                .setTitle(defaultTitle || '')
                .setBody(defaultBody || '');
        }

        //  Add listeners
        this
            .addListener(this.close, 'click', (e) => {
                this.hide();
            })
            .addListener(document, 'keyup', (e) => {
                if (e.key === 'Escape' && this.isShown()) {
                    this.hide();
                    e.preventDefault();
                }
            });

        if (!options.el) {
            //  Compile
            this.inner.appendChild(this.close);
            this.inner.appendChild(this.title);
            this.inner.appendChild(this.body);
            this.container.appendChild(this.inner)

            //  Add to DOM
            document.body.appendChild(this.container);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a new div element
     * @returns {HTMLDivElement}
     */
    newDiv(className, body) {
        let el = document.createElement('div');
        if (className) {
            this.addClass(el, className);
        }
        if (body) {
            el.innerHTML = body;
        }
        return el;
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a class to an element
     * @param {HTMLElement} el The element
     * @param {string} className The class name
     * @returns {Instance}
     */
    addClass(el, className) {
        el.classList.add(this.classes[className]);
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a style to an element
     * @param {HTMLElement} el The element
     * @param {string} style The style
     * @param {mixed} value The value
     * @returns {Instance}
     */
    addStyle(el, style, value) {
        el.style[style] = value;
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Adds an event listener to an element
     * @param {HTMLElement} el The element
     * @param {string} event The event
     * @param {function} callback The callback
     * @returns {Instance}
     */
    addListener(el, event, callback) {
        el.addEventListener(event, callback);
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Shows the modal
     * @returns {Instance}
     */
    show() {
        this.container.classList.add(this.classes.show);
        document.body.classList.add('noscroll');
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Whetehr modal is currently being shown
     * @returns {boolean}
     */
    isShown() {
        return this.container.classList.contains(this.classes.show);
    }

    // --------------------------------------------------------------------------

    /**
     * Hide the modal
     * @returns {Instance}
     */
    hide() {
        this.container.classList.remove(this.classes.show);
        document.body.classList.remove('noscroll');
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the modal's title
     * @param {string} title
     * @returns {Instance}
     */
    setTitle(title) {
        this.title.innerHTML = title;
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the modal's body
     * @param {Array|HTMLElement|string} elements The body
     * @returns {Instance}
     */
    setBody(elements) {
        this.body.innerHTML = '';

        if (Array.isArray(elements)) {
            elements.map((element) => {
                this.body.append(element);
            });

        } else if (typeof elements === 'string') {
            this.body.innerHTML = elements;

        } else {
            this.body.append(elements);
        }

        this.adminController.refreshUi(this.body);

        return this;
    }
}

export default Instance;
