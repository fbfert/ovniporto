# Varredura de segredos (gitleaks)

- Data: 2026-10-04
- Comando: `make secrets` (gitleaks em container, sobre todo o histórico do Git, valores mascarados)
- Resultado: `37 commits scanned` · `no leaks found`

Credenciais e chaves entram só por variáveis de ambiente (`.env` na VPS, fora do Git; `.env.example` só com nomes e valores vazios).
