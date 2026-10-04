# Design

## Context

Ver proposal.md. Dados ficam em VPS no Brasil; a métrica Umami e o backup são self-hosted ou em bucket controlado pelo projeto.

## Goals / Non-Goals

**Goals:** deploy de um comando, recuperação documentada, nenhuma credencial no Git.

**Non-Goals:** orquestração (Kubernetes), múltiplas réplicas, CDN externa.

## Decisions

- **Proxy reverso:** usar o que já existir na VPS (Caddy ou Traefik), verificado antes de implementar; Caddy como padrão se não houver nenhum.
- **Imagem multi-stage:** estágio Node gera assets cliente e SSR; estágio PHP final sem Node; container `ssr` separado com Node mínimo.
- **Rollback por tag de imagem:** o script marca a imagem atual como `previous` antes de subir a nova e volta a ela se `/up` falhar.
- **Backup:** dump MySQL + tar do storage, criptografados com `age` (chave pública na VPS, privada fora dela), enviados ao **Google Drive** do projeto com `rclone` (remote configurado na VPS, pasta dedicada). O Drive não tem regra de expiração: o próprio script apaga os arquivos com mais de 30 dias (`rclone delete --min-age 30d`) a cada execução. O arquivo vai cifrado, então o Google nunca vê CPF nem e-mails.
- **Painéis:** Laravel Horizon (filas e falhas, substitui o `queue:work` do worker) e Laravel Pulse (saúde), ambos só para `admin`.
- **Privacidade:** logs do nginx com retenção de 6 meses (definida em `harden-privacy-lgpd`); backups criptografados porque contêm CPF e e-mails.

## Risks / Trade-offs

- [CSP quebra o SDK do PayPal] → CSP primeiro em modo report-only no staging, depois aplicada.
- [Migração destrutiva impede rollback] → migrações somente aditivas; remoções em deploy posterior.

## Open Questions

- Qual proxy reverso já roda na VPS (verificar no início da implementação; não muda specs).
