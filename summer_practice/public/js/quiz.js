const questionsPerGame = 10;
let currentQuestion = null;
let answeredQuestions = [];
let correctCount = 0;
let questionNumber = 0;

function loadQuestion(type) {
    const exclude = answeredQuestions.join(',');
    fetch(`/api/question?type=${type}&exclude=${exclude}`)
        .then(response => response.json())
        .then(data => {
            if (data.error || data.finished) {
                finishGame();
                return;
            }
            currentQuestion = data;
            displayQuestion(data);
        })
        .catch(error => {
            console.error('Error loading question:', error);
            finishGame();
        });
}

function displayQuestion(data) {
    const container = document.getElementById('question');
    const questionData = data.question_data;
    
    let html = `<div class="question-text">${questionData.question_text}</div>`;
    html += '<div class="options">';
    
    data.options.forEach((option) => {
        const label = option.name || option.capital || 'Option';
        const safeLabel = label.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        html += `<button class="option-btn" onclick="submitAnswer('${safeLabel}')">${label}</button>`;
    });
    
    html += '</div>';
    container.innerHTML = html;
    
    document.getElementById('question-number').textContent = questionNumber + 1;
    document.getElementById('result').style.display = 'none';
}

function submitAnswer(answer) {
    const submitBtn = document.querySelector('.option-btn');
    document.querySelectorAll('.option-btn').forEach(btn => btn.disabled = true);
    
    fetch('/api/answer', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            question_id: currentQuestion.id,
            answer: answer
        })
    })
    .then(response => response.json())
    .then(data => {
        const resultDiv = document.getElementById('result');
        const messageDiv = resultDiv.querySelector('.result-message');
        
        if (data.is_correct) {
            correctCount++;
            messageDiv.textContent = '✅ Правильно!';
            messageDiv.className = 'correct';
        } else {
            const correctAnswer = data.correct_answer || 'неизвестно';
            messageDiv.textContent = `❌ Неправильно. Правильный ответ: ${correctAnswer}`;
            messageDiv.className = 'wrong';
        }
        
        document.getElementById('correct-count').textContent = correctCount;
        document.getElementById('question').querySelector('.options').style.display = 'none';
        resultDiv.style.display = 'block';
        
        answeredQuestions.push(currentQuestion.id);
        questionNumber++;
    })
    .catch(error => {
        console.error('Error submitting answer:', error);
        alert('Произошла ошибка при проверке ответа');
    });
}

function nextQuestion() {
    if (questionNumber >= questionsPerGame) {
        finishGame();
    } else {
        document.querySelectorAll('.option-btn').forEach(btn => btn.disabled = false);
        loadQuestion(window.quizType);
    }
}

function finishGame() {
    const container = document.getElementById('quiz-container');
    const percentage = questionNumber > 0 ? Math.round((correctCount / questionNumber) * 100) : 0;
    
    container.innerHTML = `
        <h2>🎉 Игра завершена!</h2>
        <p>Правильных ответов: ${correctCount} из ${questionNumber}</p>
        <p>Процент правильных ответов: ${percentage}%</p>
        <div style="margin-top: 20px;">
            <a href="/quiz" class="btn">Вернуться к выбору</a>
            <a href="/quiz/play?type=${window.quizType}" class="btn btn-secondary">Играть ещё раз</a>
        </div>
    `;
}

document.addEventListener('DOMContentLoaded', function() {
    const typeElement = document.querySelector('[data-quiz-type]');
    if (typeElement) {
        window.quizType = typeElement.dataset.quizType;
        loadQuestion(window.quizType);
    }
});