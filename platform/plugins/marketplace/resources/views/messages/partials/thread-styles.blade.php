<style>
    .marketplace-thread-wrapper {
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        background: #fff;
    }

    .marketplace-thread__messages {
        max-height: 60vh;
        overflow-y: auto;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 12px 12px 0 0;
    }

    .marketplace-thread__message {
        margin-bottom: 1rem;
        max-width: 82%;
    }

    .marketplace-thread__message--outgoing {
        margin-left: auto;
    }

    .marketplace-thread__meta {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        margin-bottom: 0.35rem;
        font-size: 0.85rem;
        color: #6c757d;
    }

    .marketplace-thread__message--outgoing .marketplace-thread__meta {
        justify-content: flex-end;
    }

    .marketplace-thread__bubble {
        border-radius: 14px;
        padding: 0.85rem 1rem;
        background: #fff;
        border: 1px solid #e4e7eb;
        line-height: 1.6;
        white-space: normal;
    }

    .marketplace-thread__message--outgoing .marketplace-thread__bubble {
        background: #e8f1ff;
        border-color: #cfe0ff;
    }

    .marketplace-thread__composer {
        padding: 1rem;
        border-top: 1px solid var(--bs-border-color);
    }
</style>
