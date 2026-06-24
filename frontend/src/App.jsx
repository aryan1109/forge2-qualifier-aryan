import { useEffect, useMemo, useState } from 'react';

const API_BASE = (import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api').replace(/\/$/, '');

function sortByPosition(items = []) {
  return [...items].sort((a, b) => {
    const positionDelta = (a.position ?? 0) - (b.position ?? 0);
    return positionDelta || (a.id ?? 0) - (b.id ?? 0);
  });
}

function normalizeBoard(board) {
  return {
    ...board,
    lists: sortByPosition(board.lists || []).map((list) => ({
      ...list,
      cards: sortByPosition(list.cards || []),
    })),
  };
}

async function apiRequest(path, options = {}) {
  const response = await fetch(`${API_BASE}${path}`, {
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(options.headers || {}),
    },
    ...options,
    body: options.body && typeof options.body !== 'string' ? JSON.stringify(options.body) : options.body,
  });

  if (!response.ok) {
    const message = await response.text();
    throw new Error(message || `Request failed with ${response.status}`);
  }

  if (response.status === 204) {
    return null;
  }

  return response.json();
}

function cleanDate(value) {
  return value ? String(value).slice(0, 10) : '';
}

export default function App() {
  const [board, setBoard] = useState(null);
  const [status, setStatus] = useState('loading');
  const [error, setError] = useState('');
  const [saving, setSaving] = useState(false);

  const listOptions = useMemo(() => board?.lists || [], [board]);

  async function loadBoard({ quiet = false } = {}) {
    if (!quiet) {
      setStatus('loading');
    }

    try {
      const data = await apiRequest('/boards/default');
      setBoard(normalizeBoard(data));
      setError('');
      setStatus('ready');
    } catch (err) {
      setError(err.message);
      setStatus('error');
    }
  }

  async function runMutation(action) {
    setSaving(true);
    setError('');

    try {
      await action();
      await loadBoard({ quiet: true });
    } catch (err) {
      setError(err.message);
    } finally {
      setSaving(false);
    }
  }

  useEffect(() => {
    loadBoard();
  }, []);

  function createCard(listId, payload) {
    return runMutation(() => apiRequest(`/lists/${listId}/cards`, {
      method: 'POST',
      body: payload,
    }));
  }

  function updateCard(card) {
    return runMutation(() => apiRequest(`/cards/${card.id}`, {
      method: 'PATCH',
      body: {
        title: card.title,
        description: card.description,
        due_date: card.due_date || null,
      },
    }));
  }

  function moveCard(cardId, listId) {
    return runMutation(() => apiRequest(`/cards/${cardId}/move`, {
      method: 'POST',
      body: {
        list_id: Number(listId),
      },
    }));
  }

  return (
    <main className="app-shell">
      <header className="topbar">
        <div>
          <p className="eyebrow">Forge 2 Qualifier</p>
          <h1>{board?.title || 'Forge 2 Kanban'}</h1>
        </div>
        <button type="button" className="ghost-button" onClick={() => loadBoard()} disabled={status === 'loading'}>
          Refresh
        </button>
      </header>

      {error && (
        <section className="notice" role="alert">
          <strong>API connection needs attention.</strong>
          <span>{error}</span>
        </section>
      )}

      {status === 'loading' && <p className="loading">Loading board...</p>}

      {board && (
        <section className="board-grid" aria-label="Kanban board">
          {board.lists.map((list) => (
            <section className="list-panel" key={list.id}>
              <div className="list-header">
                <h2>{list.title}</h2>
                <span>{list.cards.length}</span>
              </div>

              <CardComposer disabled={saving} onCreate={(payload) => createCard(list.id, payload)} />

              <div className="card-stack">
                {list.cards.length === 0 && <p className="empty-state">No cards yet.</p>}

                {list.cards.map((card) => (
                  <CardEditor
                    card={card}
                    disabled={saving}
                    key={card.id}
                    lists={listOptions}
                    onMove={moveCard}
                    onSave={updateCard}
                  />
                ))}
              </div>
            </section>
          ))}
        </section>
      )}
    </main>
  );
}

function CardComposer({ disabled, onCreate }) {
  const [title, setTitle] = useState('');

  async function handleSubmit(event) {
    event.preventDefault();
    const trimmed = title.trim();

    if (!trimmed) {
      return;
    }

    await onCreate({ title: trimmed, description: '', due_date: null });
    setTitle('');
  }

  return (
    <form className="composer" onSubmit={handleSubmit}>
      <input
        aria-label="New card title"
        disabled={disabled}
        onChange={(event) => setTitle(event.target.value)}
        placeholder="New card title"
        value={title}
      />
      <button type="submit" disabled={disabled || !title.trim()}>
        Add
      </button>
    </form>
  );
}

function CardEditor({ card, disabled, lists, onMove, onSave }) {
  const [draft, setDraft] = useState({
    ...card,
    due_date: cleanDate(card.due_date),
    description: card.description || '',
  });

  useEffect(() => {
    setDraft({
      ...card,
      due_date: cleanDate(card.due_date),
      description: card.description || '',
    });
  }, [card]);

  const isDirty =
    draft.title !== card.title ||
    draft.description !== (card.description || '') ||
    draft.due_date !== cleanDate(card.due_date);

  function setField(field, value) {
    setDraft((current) => ({
      ...current,
      [field]: value,
    }));
  }

  async function handleSubmit(event) {
    event.preventDefault();
    await onSave(draft);
  }

  return (
    <form className="card" onSubmit={handleSubmit}>
      <label>
        <span>Title</span>
        <input
          disabled={disabled}
          onChange={(event) => setField('title', event.target.value)}
          required
          value={draft.title}
        />
      </label>

      <label>
        <span>Description</span>
        <textarea
          disabled={disabled}
          onChange={(event) => setField('description', event.target.value)}
          rows="3"
          value={draft.description}
        />
      </label>

      <div className="card-row">
        <label>
          <span>Due</span>
          <input
            disabled={disabled}
            onChange={(event) => setField('due_date', event.target.value)}
            type="date"
            value={draft.due_date}
          />
        </label>

        <label>
          <span>Move</span>
          <select
            disabled={disabled}
            onChange={(event) => onMove(card.id, event.target.value)}
            value={card.list_id}
          >
            {lists.map((list) => (
              <option key={list.id} value={list.id}>
                {list.title}
              </option>
            ))}
          </select>
        </label>
      </div>

      <button type="submit" disabled={disabled || !draft.title.trim() || !isDirty}>
        Save
      </button>
    </form>
  );
}
