// src/app.js

document.addEventListener('DOMContentLoaded', () => {
    fetchAccounts();
    fetchTransactions();

    document.getElementById('btn-add-account').addEventListener('click', toggleAccountForm);
    document.getElementById('btn-add-transaction').addEventListener('click', toggleTransactionForm);

    document.getElementById('form-account').addEventListener('submit', handleAddAccount);
    document.getElementById('form-transaction').addEventListener('submit', handleAddTransaction);
});

function toggleAccountForm() {
    document.getElementById('add-account-form').classList.toggle('hidden');
}

function toggleTransactionForm() {
    document.getElementById('add-transaction-form').classList.toggle('hidden');
}

async function fetchAccounts() {
    try {
        const response = await fetch('api/accounts.php');
        const accounts = await response.json();

        const list = document.getElementById('accounts-list');
        list.innerHTML = '';

        const select = document.getElementById('transaction-account-select');
        select.innerHTML = '';

        let totalBalance = 0;

        accounts.forEach(acc => {
            // Populate List
            const item = document.createElement('div');
            item.className = 'list-item';
            item.innerHTML = `
                <div class="info">
                    <span class="name">${acc.name}</span>
                    <span class="meta">${acc.type}</span>
                </div>
                <div class="value ${acc.balance >= 0 ? 'positive' : 'negative'}">
                    $${parseFloat(acc.balance).toFixed(2)}
                </div>
            `;
            list.appendChild(item);

            // Populate Select Dropdown
            const option = document.createElement('option');
            option.value = acc.id;
            option.textContent = `${acc.name} (${acc.type})`;
            select.appendChild(option);

            // Calculate Total (Simplified)
            // In real accounting, Liabilities/Equity are credit balances, Assets are debit.
            // For this prototype, we just sum them up, but let's try to be slightly smart.
            // If it's an asset, it adds to net worth. If liability, it subtracts.
            if (acc.type === 'Asset') {
                totalBalance += parseFloat(acc.balance);
            } else if (acc.type === 'Liability') {
                totalBalance -= parseFloat(acc.balance);
            } else {
                // For now just add everything else or ignore
                totalBalance += parseFloat(acc.balance);
            }
        });

        document.getElementById('total-balance').textContent = `$${totalBalance.toFixed(2)}`;

    } catch (error) {
        console.error('Error fetching accounts:', error);
    }
}

async function fetchTransactions() {
    try {
        const response = await fetch('api/transactions.php');
        const transactions = await response.json();

        const list = document.getElementById('transactions-list');
        list.innerHTML = '';

        transactions.forEach(tx => {
            const item = document.createElement('div');
            item.className = 'list-item';

            const isExpense = tx.type === 'Expense';
            const sign = isExpense ? '-' : '+';
            const colorClass = isExpense ? 'negative' : 'positive';

            item.innerHTML = `
                <div class="info">
                    <span class="name">${tx.description}</span>
                    <span class="meta">${tx.account_name} • ${tx.date}</span>
                </div>
                <div class="value ${colorClass}">
                    ${sign}$${parseFloat(tx.amount).toFixed(2)}
                </div>
            `;
            list.appendChild(item);
        });

    } catch (error) {
        console.error('Error fetching transactions:', error);
    }
}

async function handleAddAccount(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    // Convert balance to valid float
    if (!data.balance) data.balance = 0;

    try {
        const response = await fetch('api/accounts.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            e.target.reset();
            toggleAccountForm();
            fetchAccounts();
        } else {
            alert('Error creating account');
        }
    } catch (error) {
        console.error(error);
    }
}

async function handleAddTransaction(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    try {
        const response = await fetch('api/transactions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            e.target.reset();
            toggleTransactionForm();
            fetchTransactions();
            fetchAccounts(); // Update balances
        } else {
            alert('Error creating transaction');
        }
    } catch (error) {
        console.error(error);
    }
}
