import { useState } from 'react'
import './App.css'

const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'

const BADGE_EMOJI = {
  Beginner: '🌱',
  Bronze:   '🥉',
  Silver:   '🥈',
  Gold:     '🥇',
}

const ACHIEVEMENT_EMOJI = {
  'First Purchase':  '🛍️',
  '5 Purchases':     '⭐',
  '10 Purchases':    '🌟',
  '25 Purchases':    '🏅',
  '50 Purchases':    '🏆',
}

const ALL_ACHIEVEMENTS = [
  'First Purchase',
  '5 Purchases',
  '10 Purchases',
  '25 Purchases',
  '50 Purchases',
]

const BADGE_ORDER = ['Beginner', 'Bronze', 'Silver', 'Gold']

function ProgressBar({ current, max }) {
  const pct = max > 0 ? Math.min(100, Math.round((current / max) * 100)) : 100
  return (
    <div className="progress-bar-track" role="progressbar" aria-valuenow={pct} aria-valuemin={0} aria-valuemax={100}>
      <div className="progress-bar-fill" style={{ width: `${pct}%` }} />
    </div>
  )
}

function BadgeCard({ data }) {
  const { current_badge, next_badge, remaining_to_unlock_next_badge, unlocked_achievements } = data
  const badgeOrderIdx  = BADGE_ORDER.indexOf(current_badge)
  const nextBadgeIdx   = BADGE_ORDER.indexOf(next_badge)
  const totalForNext   = next_badge ? (remaining_to_unlock_next_badge + unlocked_achievements.length) : unlocked_achievements.length
  const progressCurrent = next_badge ? unlocked_achievements.length : unlocked_achievements.length
  const progressMax     = next_badge ? totalForNext : unlocked_achievements.length

  return (
    <div className="card">
      <div className="card-header">
        <span className="card-icon">🎖️</span>
        <h3 className="card-title">Current Badge</h3>
      </div>
      <div className="card-body">
        <div className="badge-display">
          <div className={`badge-icon-wrap ${current_badge}`}>
            {BADGE_EMOJI[current_badge] || '🎖️'}
          </div>
          <div className="badge-info">
            <h2 className={current_badge}>{current_badge}</h2>
            <div style={{ color: 'var(--color-muted)', fontSize: '0.875rem' }}>
              {unlocked_achievements.length} achievement{unlocked_achievements.length !== 1 ? 's' : ''} unlocked
            </div>
          </div>

          {next_badge && (
            <div className="next-badge-info">
              <div className="label">Next Badge</div>
              <div className="value">{BADGE_EMOJI[next_badge]} {next_badge}</div>
              <div className="count">
                {remaining_to_unlock_next_badge} more achievement{remaining_to_unlock_next_badge !== 1 ? 's' : ''} needed
              </div>
            </div>
          )}

          {!next_badge && (
            <div className="next-badge-info">
              <div className="label">Status</div>
              <div className="value" style={{ color: '#ca8a04' }}>🏆 Max Level!</div>
            </div>
          )}
        </div>

        {next_badge && (
          <div className="progress-section">
            <div className="progress-label">
              <span>Progress to {next_badge}</span>
              <span>{progressCurrent} / {progressMax}</span>
            </div>
            <ProgressBar current={progressCurrent} max={progressMax} />
          </div>
        )}
      </div>
    </div>
  )
}

function AchievementsCard({ data }) {
  const { unlocked_achievements, next_available_achievements } = data

  return (
    <div className="card">
      <div className="card-header">
        <span className="card-icon">🏅</span>
        <h3 className="card-title">Achievements</h3>
      </div>
      <div className="card-body">
        {unlocked_achievements.length === 0 && next_available_achievements.length === 0 ? (
          <p className="empty-msg">No achievements yet. Make your first purchase!</p>
        ) : (
          <div className="achievement-list">
            {ALL_ACHIEVEMENTS.map((name) => {
              const isUnlocked = unlocked_achievements.includes(name)
              const isNext     = next_available_achievements.includes(name)
              return (
                <span
                  key={name}
                  className={`achievement-chip ${isUnlocked ? 'unlocked' : isNext ? 'next' : 'locked'}`}
                  title={isUnlocked ? 'Unlocked!' : isNext ? 'Next to unlock' : 'Locked'}
                >
                  {isUnlocked ? '✅' : isNext ? '⏳' : '🔒'}
                  {ACHIEVEMENT_EMOJI[name] || ''} {name}
                </span>
              )
            })}
          </div>
        )}
      </div>
    </div>
  )
}

function StatsCard({ data }) {
  const { unlocked_achievements, current_badge } = data
  const total = ALL_ACHIEVEMENTS.length

  return (
    <div className="two-col">
      <div className="card">
        <div className="card-header">
          <span className="card-icon">📊</span>
          <h3 className="card-title">Completion</h3>
        </div>
        <div className="card-body">
          <div style={{ fontSize: '2.5rem', fontWeight: 800, color: 'var(--color-primary)', lineHeight: 1 }}>
            {Math.round((unlocked_achievements.length / total) * 100)}%
          </div>
          <div style={{ color: 'var(--color-muted)', marginTop: '0.4rem', fontSize: '0.875rem' }}>
            {unlocked_achievements.length} of {total} achievements
          </div>
          <div className="progress-section">
            <ProgressBar current={unlocked_achievements.length} max={total} />
          </div>
        </div>
      </div>

      <div className="card">
        <div className="card-header">
          <span className="card-icon">🎯</span>
          <h3 className="card-title">Badge Level</h3>
        </div>
        <div className="card-body">
          <div style={{ fontSize: '2.5rem', fontWeight: 800, color: 'var(--color-primary)', lineHeight: 1 }}>
            {BADGE_ORDER.indexOf(current_badge) + 1}
            <span style={{ fontSize: '1rem', fontWeight: 500, color: 'var(--color-muted)' }}>
              &nbsp;/ {BADGE_ORDER.length}
            </span>
          </div>
          <div style={{ color: 'var(--color-muted)', marginTop: '0.4rem', fontSize: '0.875rem' }}>
            {current_badge} tier
          </div>
          <div className="progress-section">
            <ProgressBar current={BADGE_ORDER.indexOf(current_badge) + 1} max={BADGE_ORDER.length} />
          </div>
        </div>
      </div>
    </div>
  )
}

export default function App() {
  const [userId, setUserId]   = useState('1')
  const [inputId, setInputId] = useState('1')
  const [data, setData]       = useState(null)
  const [loading, setLoading] = useState(false)
  const [error, setError]     = useState(null)

  async function loadUser() {
    if (!inputId || isNaN(Number(inputId))) return
    setLoading(true)
    setError(null)
    setData(null)
    try {
      const res = await fetch(`${API_BASE}/api/users/${inputId}/achievements`)
      if (res.status === 404) throw new Error(`User #${inputId} not found.`)
      if (!res.ok) throw new Error(`Server error: ${res.status}`)
      const json = await res.json()
      setData(json)
      setUserId(inputId)
    } catch (e) {
      setError(e.message)
    } finally {
      setLoading(false)
    }
  }

  function handleKeyDown(e) {
    if (e.key === 'Enter') loadUser()
  }

  return (
    <div className="app">
      <header className="header">
        <div>
          <div className="header-logo">🛍️ Bumpa Loyalty</div>
          <div className="header-subtitle">Customer Achievement Dashboard</div>
        </div>
      </header>

      <main className="main">
        <div className="user-selector">
          <label htmlFor="user-id-input">User ID:</label>
          <input
            id="user-id-input"
            type="number"
            min="1"
            value={inputId}
            onChange={e => setInputId(e.target.value)}
            onKeyDown={handleKeyDown}
            placeholder="e.g. 1"
          />
          <button className="btn-load" onClick={loadUser} disabled={loading}>
            {loading ? 'Loading…' : 'Load'}
          </button>
          <p className="demo-hint">
            Demo users: 1 (Beginner), 2 (Beginner+1), 3 (Bronze), 4 (Gold)
          </p>
        </div>

        {loading && (
          <div className="state-msg">Loading achievements for user #{inputId}…</div>
        )}

        {error && !loading && (
          <div className="state-msg error">⚠️ {error}</div>
        )}

        {!loading && !error && data && (
          <div className="dashboard">
            <BadgeCard data={data} />
            <AchievementsCard data={data} />
            <StatsCard data={data} />
          </div>
        )}

        {!loading && !error && !data && (
          <div className="state-msg">Enter a user ID above and click <strong>Load</strong> to view achievements.</div>
        )}
      </main>
    </div>
  )
}
