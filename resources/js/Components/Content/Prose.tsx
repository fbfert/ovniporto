/**
 * Markdown rendered and sanitized on the server (raw HTML stripped, unsafe
 * links dropped by the MarkdownRenderer), shown with the reading typography.
 */
export function Prose({ html, className = '' }: { html: string; className?: string }) {
    return <div className={`prose-ovni ${className}`} dangerouslySetInnerHTML={{ __html: html }} />;
}
