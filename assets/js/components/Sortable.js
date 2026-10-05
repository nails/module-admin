/* export Sortable */

/* globals $, jQuery */

const DRAG_THRESHOLD = 4;
const SCROLL_EDGE = 64;
const SCROLL_STEP = 16;

class Sortable {

    /**
     * Construct Sortable
     * @return {Sortable}
     */
    constructor(adminController) {

        adminController
            .onRefreshUi((e, domElement) => {
                this.init(domElement);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Initialise
     * @param {HTMLElement} domElement
     * @returns {Sortable}
     */
    init(domElement) {
        $('.js-admin-sortable:not(.js-admin-sortable--processed)', domElement)
            .addClass('js-admin-sortable--processed')
            .each((index, element) => {
                this.bind(element);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Bind pointer sorting for one container.
     * The preview is a clone that stays under the same parent as the table,
     * so selectors that style the row still match. The real row is what lands.
     * @param {HTMLElement} element
     * @returns {void}
     */
    bind(element) {
        let $item = $(element);
        let handle = $item.data('handle') || null;
        let axis = $item.data('axis') === 'x' ? 'x' : 'y';
        let session = null;

        $item.on('sortable:sort', () => {
            $item
                .find('.js-admin-sortable__order')
                .each(function(index) {
                    $(this).val(index);
                });
        });

        if (handle) {
            $item.on('pointerover', handle, function() {
                this.classList.add('js-admin-sortable__handle');
            });
        }

        let start = (event) => {
            if (session || (event.button !== undefined && event.button !== 0)) {
                return;
            }

            let item = this.itemFromEvent(element, handle, event);
            if (!item) {
                return;
            }

            event.preventDefault();

            let rect = item.getBoundingClientRect();
            let blockSelect = (selectEvent) => {
                selectEvent.preventDefault();
            };

            session = {
                pointerId: event.pointerId,
                item: item,
                axis: axis,
                startX: event.clientX,
                startY: event.clientY,
                lastX: event.clientX,
                lastY: event.clientY,
                originLeft: rect.left,
                originTop: rect.top,
                grabX: event.clientX - rect.left,
                grabY: event.clientY - rect.top,
                active: false,
                frame: 0,
                helper: null,
                placeholder: null,
                userSelect: document.body.style.userSelect,
                blockSelect: blockSelect
            };

            document.body.classList.add('js-admin-sortable--dragging');
            document.body.style.userSelect = 'none';
            document.addEventListener('selectstart', blockSelect);

            let move = (moveEvent) => {
                this.move(element, session, moveEvent);
            };
            let end = (endEvent) => {
                if (!session || endEvent.pointerId !== session.pointerId) {
                    return;
                }
                document.removeEventListener('pointermove', move);
                document.removeEventListener('pointerup', end);
                document.removeEventListener('pointercancel', end);
                document.removeEventListener('selectstart', blockSelect);
                this.finish(session, $item);
                session = null;
            };

            document.addEventListener('pointermove', move);
            document.addEventListener('pointerup', end);
            document.addEventListener('pointercancel', end);

            try {
                element.setPointerCapture(event.pointerId);
            } catch (ignore) {
                // Synthetic events and a few browsers reject capture.
            }
        };

        // mousedown is what starts a text selection. pointerdown owns the gesture.
        element.addEventListener('mousedown', (event) => {
            if (event.button !== 0) {
                return;
            }
            if (this.itemFromEvent(element, handle, event)) {
                event.preventDefault();
            }
        }, true);

        element.addEventListener('pointerdown', start);
    }

    // --------------------------------------------------------------------------

    /**
     * Direct child of the container that owns this pointer event.
     * @param {HTMLElement} container
     * @param {string|null} handle
     * @param {Event} event
     * @returns {HTMLElement|null}
     */
    itemFromEvent(container, handle, event) {
        if (handle) {
            let grip = event.target.closest(handle);
            if (!grip || !container.contains(grip)) {
                return null;
            }
        } else if (event.target.closest('input, textarea, select, button, a, label')) {
            return null;
        }

        let node = event.target;
        while (node && node.parentElement !== container) {
            node = node.parentElement;
        }

        return node || null;
    }

    // --------------------------------------------------------------------------

    /**
     * Track the pointer and reorder once it has actually moved.
     * @param {HTMLElement} container
     * @param {Object} session
     * @param {PointerEvent} event
     * @returns {void}
     */
    move(container, session, event) {
        if (!session || event.pointerId !== session.pointerId) {
            return;
        }

        session.lastX = event.clientX;
        session.lastY = event.clientY;

        if (!session.active) {
            let dx = Math.abs(event.clientX - session.startX);
            let dy = Math.abs(event.clientY - session.startY);
            let distance = session.axis === 'x' ? dx : dy;
            if (distance < DRAG_THRESHOLD) {
                return;
            }
            session.active = true;
            this.lift(session);
            let selection = window.getSelection();
            if (selection) {
                selection.removeAllRanges();
            }
            session.frame = window.requestAnimationFrame(() => {
                this.tick(container, session);
            });
        }

        event.preventDefault();
        this.position(session);
        this.place(container, session.placeholder, event.clientX, event.clientY, session.axis);
    }

    // --------------------------------------------------------------------------

    /**
     * Keep scrolling, and reordering, while the pointer is held at the edge.
     * @param {HTMLElement} container
     * @param {Object} session
     * @returns {void}
     */
    tick(container, session) {
        if (!session.active) {
            return;
        }

        if (this.scroll(container, session.lastX, session.lastY, session.axis)) {
            this.position(session);
            this.place(container, session.placeholder, session.lastX, session.lastY, session.axis);
        }

        session.frame = window.requestAnimationFrame(() => {
            this.tick(container, session);
        });
    }

    // --------------------------------------------------------------------------

    /**
     * Show a floating copy of the grabbed row and leave a gap where it was.
     * @param {Object} session
     * @returns {void}
     */
    lift(session) {
        let item = session.item;
        let rect = item.getBoundingClientRect();
        let table = item.tagName === 'TR' ? item.closest('table') : null;
        let helper;

        session.placeholder = this.placeholder(item, rect);
        item.parentElement.insertBefore(session.placeholder, item);

        if (table) {
            helper = document.createElement('table');
            helper.className = (table.className + ' js-admin-sortable__helper').trim();
            let body = document.createElement('tbody');
            let clone = item.cloneNode(true);
            this.prepareClone(item, clone);
            body.appendChild(clone);
            helper.appendChild(body);
            table.parentElement.appendChild(helper);
        } else {
            helper = item.cloneNode(true);
            this.prepareClone(item, helper);
            helper.classList.add('js-admin-sortable__helper');
            item.parentElement.appendChild(helper);
        }

        helper.style.setProperty('position', 'fixed', 'important');
        helper.style.setProperty('width', rect.width + 'px', 'important');
        helper.style.setProperty('max-width', 'none', 'important');
        helper.style.setProperty('margin', '0', 'important');
        helper.style.setProperty('z-index', '30', 'important');
        helper.style.setProperty('pointer-events', 'none', 'important');
        helper.setAttribute('aria-hidden', 'true');

        session.helper = helper;
        item.classList.add('js-admin-sortable__source');
        this.position(session);
    }

    // --------------------------------------------------------------------------

    /**
     * Gap left in the list while the copy follows the pointer.
     * @param {HTMLElement} item
     * @param {DOMRect} rect
     * @returns {HTMLElement}
     */
    placeholder(item, rect) {
        let node = document.createElement(item.tagName.toLowerCase());
        node.className = 'js-admin-sortable__placeholder';
        node.style.setProperty('--sortable-row-width', rect.width + 'px');

        if (item.tagName === 'TR') {
            Array.from(item.children).forEach((cell) => {
                let gap = document.createElement(cell.tagName.toLowerCase());
                gap.colSpan = cell.colSpan;
                gap.style.height = rect.height + 'px';
                gap.innerHTML = '&nbsp;';
                node.appendChild(gap);
            });
        } else {
            node.style.height = rect.height + 'px';
        }

        return node;
    }

    // --------------------------------------------------------------------------

    /**
     * Make the copy visual only: same field values, no submitted names.
     * @param {HTMLElement} source
     * @param {HTMLElement} clone
     * @returns {void}
     */
    prepareClone(source, clone) {
        clone.querySelectorAll('[id]').forEach((node) => {
            node.removeAttribute('id');
        });

        let from = source.querySelectorAll('input, textarea, select');
        let to = clone.querySelectorAll('input, textarea, select');
        from.forEach((field, index) => {
            let target = to[index];
            if (!target) {
                return;
            }
            if (target.tagName === 'TEXTAREA') {
                target.textContent = field.value;
            }
            target.value = field.value;
            target.checked = field.checked;
            target.disabled = true;
            target.removeAttribute('name');
        });

        if (source.tagName !== 'TR') {
            return;
        }

        let cells = Array.from(source.children);
        Array.from(clone.children).forEach((cell, index) => {
            if (!cells[index]) {
                return;
            }
            cell.style.setProperty('width', cells[index].getBoundingClientRect().width + 'px', 'important');
            cell.style.boxSizing = 'border-box';
        });
    }

    // --------------------------------------------------------------------------

    /**
     * Park the copy under the pointer, locked to the sort axis.
     * @param {Object} session
     * @returns {void}
     */
    position(session) {
        if (!session.helper) {
            return;
        }

        let left = session.axis === 'y' ? session.originLeft : session.lastX - session.grabX;
        let top = session.axis === 'x' ? session.originTop : session.lastY - session.grabY;
        session.helper.style.setProperty('left', left + 'px', 'important');
        session.helper.style.setProperty('top', top + 'px', 'important');
    }

    // --------------------------------------------------------------------------

    /**
     * Swap the gap with the neighbouring row once the pointer crosses its midpoint.
     * @param {HTMLElement} container
     * @param {HTMLElement} slot
     * @param {number} x
     * @param {number} y
     * @param {string} axis
     * @returns {void}
     */
    place(container, slot, x, y, axis) {
        if (!slot || slot.parentElement !== container) {
            return;
        }

        let pointer = axis === 'x' ? x : y;
        let guard = container.children.length;

        while (guard > 0) {
            guard -= 1;
            let next = this.sibling(slot, 'nextElementSibling');
            let previous = this.sibling(slot, 'previousElementSibling');

            if (next && pointer > this.midpoint(next, axis)) {
                container.insertBefore(slot, next.nextElementSibling);
                continue;
            }

            if (previous && pointer < this.midpoint(previous, axis)) {
                container.insertBefore(slot, previous);
                continue;
            }

            break;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Next real row, skipping the source row parked out of view.
     * @param {HTMLElement} node
     * @param {string} direction
     * @returns {HTMLElement|null}
     */
    sibling(node, direction) {
        let cursor = node[direction];
        while (cursor && cursor.classList.contains('js-admin-sortable__source')) {
            cursor = cursor[direction];
        }
        return cursor;
    }

    // --------------------------------------------------------------------------

    /**
     * Midpoint of an element on the sort axis.
     * @param {HTMLElement} element
     * @param {string} axis
     * @returns {number}
     */
    midpoint(element, axis) {
        let box = element.getBoundingClientRect();
        if (axis === 'x') {
            return box.left + (box.width / 2);
        }
        return box.top + (box.height / 2);
    }

    // --------------------------------------------------------------------------

    /**
     * Scroll the window or a scrolling ancestor when the pointer is at its edge.
     * @param {HTMLElement} container
     * @param {number} x
     * @param {number} y
     * @param {string} axis
     * @returns {boolean}
     */
    scroll(container, x, y, axis) {
        let node = container.parentElement;

        while (node && node !== document.documentElement) {
            if (this.scrollNode(node, x, y, axis)) {
                return true;
            }
            node = node.parentElement;
        }

        if (axis !== 'x') {
            let before = window.scrollY;
            if (y < SCROLL_EDGE) {
                window.scrollBy(0, -SCROLL_STEP);
            } else if (y > window.innerHeight - SCROLL_EDGE) {
                window.scrollBy(0, SCROLL_STEP);
            }
            return window.scrollY !== before;
        }

        return false;
    }

    // --------------------------------------------------------------------------

    /**
     * Scroll one element if it can, and the pointer is at its edge.
     * @param {HTMLElement} node
     * @param {number} x
     * @param {number} y
     * @param {string} axis
     * @returns {boolean}
     */
    scrollNode(node, x, y, axis) {
        let style = window.getComputedStyle(node);
        let box = node.getBoundingClientRect();
        let moved = false;

        if (axis !== 'x' && /(auto|scroll)/.test(style.overflowY) && node.scrollHeight > node.clientHeight) {
            let before = node.scrollTop;
            if (y < box.top + SCROLL_EDGE) {
                node.scrollTop -= SCROLL_STEP;
            } else if (y > box.bottom - SCROLL_EDGE) {
                node.scrollTop += SCROLL_STEP;
            }
            moved = node.scrollTop !== before;
        }

        if (axis === 'x' && /(auto|scroll)/.test(style.overflowX) && node.scrollWidth > node.clientWidth) {
            let before = node.scrollLeft;
            if (x < box.left + SCROLL_EDGE) {
                node.scrollLeft -= SCROLL_STEP;
            } else if (x > box.right - SCROLL_EDGE) {
                node.scrollLeft += SCROLL_STEP;
            }
            moved = node.scrollLeft !== before || moved;
        }

        return moved;
    }

    // --------------------------------------------------------------------------

    /**
     * Drop the row and write the new order.
     * @param {Object} session
     * @param {jQuery} $item
     * @returns {void}
     */
    finish(session, $item) {
        let active = session.active;
        session.active = false;
        if (session.frame) {
            window.cancelAnimationFrame(session.frame);
        }

        if (session.helper) {
            session.helper.remove();
            session.helper = null;
        }

        if (session.placeholder && session.placeholder.parentElement) {
            session.placeholder.parentElement.insertBefore(session.item, session.placeholder);
            session.placeholder.remove();
        }

        session.item.classList.remove('js-admin-sortable__source');
        document.body.classList.remove('js-admin-sortable--dragging');
        document.body.style.userSelect = session.userSelect;

        try {
            session.item.releasePointerCapture(session.pointerId);
        } catch (ignore) {
            // Capture was taken on the container, or never taken.
        }

        if (active) {
            $item.trigger('sortable:sort');
        }
    }
}

export default Sortable;
