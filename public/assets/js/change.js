:root {
    --primary-color: #6c5ce7;
    --secondary-color: #4a90e2;
    --text-primary: #2d3436;
    --text-secondary: #636e72;
    --accent-color: #ff7675;
    --success-color: #00b894;
    --domain-color: #8a2be2;
    --light-purple: #a29bfe;
    --light-blue: #74b9ff;
    --card-bg: rgba(255, 255, 255, 0.85);
    --hover-color: #f8f9fa;
    --card-hover: rgba(255, 255, 255, 0.98);
    --border-color: rgba(74, 144, 226, 0.2);
}

.hidden {
    display: none !important;
}

.records-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.record-card {
    background: var(--card-bg);
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    cursor: pointer;
}

.record-card:hover {
    background: var(--card-hover);
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.record-card h4 {
    color: var(--primary-color);
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
}

.record-card p {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 0.3rem;
}

.record-card p span {
    color: var(--text-primary);
    font-weight: 500;
}

.verification-section {
    background: var(--card-bg);
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.verification-section h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.edit-section {
    background: var(--card-bg);
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.edit-section h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
    font-weight: 500;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.2);
}

.button-group {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(108, 92, 231, 0.3);
}

.btn-secondary {
    background: white;
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

.btn-secondary:hover {
    background: var(--hover-color);
}

.btn-accent {
    background: var(--accent-color);
    color: white;
    border: none;
}

.btn-accent:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(255, 118, 117, 0.3);
}

.notification {
    position: fixed;
    top: 2rem;
    right: 2rem;
    z-index: 9999;
    max-width: 300px;
}

.notification-item {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 0.75rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.notification-item.success {
    background: rgba(0, 184, 148, 0.1);
    color: #006d56;
    border-left: 4px solid #00b894;
}

.notification-item.error {
    background: rgba(255, 118, 117, 0.1);
    color: #d63031;
    border-left: 4px solid #ff7675;
}

.notification-item.info {
    background: rgba(74, 144, 226, 0.1);
    color: #0984e3;
    border-left: 4px solid #4a90e2;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-spinner {
    width: 3rem;
    height: 3rem;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

@media (max-width: 768px) {
    .records-grid {
        grid-template-columns: 1fr;
    }
    
    .button-group {
        flex-direction: column;
    }
}    