import './bootstrap';
import Alpine from 'alpinejs';
import Sortable from 'sortablejs';

window.Alpine = Alpine;
window.Sortable = Sortable;

const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
window.api = (url, method = 'GET', body = null) =>
  fetch(url, { method, headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    body: body ? JSON.stringify(body) : null }).then(r => r.ok ? (r.status === 204 ? null : r.json()) : Promise.reject(r));

/* Notification bell */
Alpine.data('bell', (url, readUrl) => ({
  open: false, unread: 0, items: [],
  init() { this.load(); setInterval(() => this.load(), 8000); },
  async load() { try { const d = await api(url); this.unread = d.unread; this.items = d.items; } catch (e) {} },
  async markAll() { await api(readUrl, 'POST'); this.load(); },
}));

/* Kanban board */
Alpine.data('kanban', (moveUrlTpl) => ({
  init() {
    this.$el.querySelectorAll('[data-column]').forEach(col => {
      Sortable.create(col, {
        group: 'tasks', animation: 160, ghostClass: 'opacity-40', draggable: '[data-task]', disabled: this.$el.dataset.readonly === '1',
        onEnd: async (evt) => {
          const to = evt.to; const id = evt.item.dataset.task;
          const ids = [...to.querySelectorAll('[data-task]')].map(e => e.dataset.task);
          document.querySelectorAll('[data-column]').forEach(c => { const n = c.parentElement.querySelector('[data-count]'); if (n) n.textContent = c.querySelectorAll('[data-task]').length; });
          try { await api(moveUrlTpl.replace('__ID__', id), 'POST', { status: to.dataset.column, ids }); } catch (e) { location.reload(); }
        },
      });
    });
  },
}));

/* Chat */
Alpine.data('chat', (cfg) => ({
  messages: [], body: '', typing: [], lastId: 0, sending: false, lastTyped: 0,
  async init() {
    await this.poll(true); this.scroll();
    setInterval(() => this.poll(), 1500);
  },
  async poll(first = false) {
    try {
      const d = await api(cfg.fetch + (this.lastId ? `?after=${this.lastId}` : ''));
      this.typing = d.typing;
      if (d.messages.length) {
        const atBottom = this.$refs.box.scrollHeight - this.$refs.box.scrollTop - this.$refs.box.clientHeight < 80;
        this.messages.push(...d.messages); this.lastId = d.messages[d.messages.length - 1].id;
        if (atBottom || first) this.$nextTick(() => this.scroll());
      }
    } catch (e) {}
  },
  scroll() { this.$refs.box.scrollTop = this.$refs.box.scrollHeight; },
  onType() { if (Date.now() - this.lastTyped > 2000) { this.lastTyped = Date.now(); api(cfg.typing, 'POST').catch(() => {}); } },
  async send() {
    const b = this.body.trim(); if (!b || this.sending) return;
    this.sending = true; this.body = '';
    try { await api(cfg.send, 'POST', { body: b }); await this.poll(); this.scroll(); } catch (e) { this.body = b; }
    this.sending = false;
  },
}));

Alpine.start();
