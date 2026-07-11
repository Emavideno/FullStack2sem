<?php
$title = 'Викторина: ' . $type_label;
ob_start();
?>

<h1>Викторина: <?= $type_label ?></h1>

<div id="quiz-container" data-quiz-type="<?= $type ?>">
    <div id="question">
        <div class="question-text">Загрузка вопроса...</div>
        <div class="options"></div>
    </div>
    <div id="result" style="display: none; margin-top: 20px; padding: 15px; border-radius: 8px; background: #f5f5f5;">
        <div class="result-message" style="font-size: 18px;"></div>
        <button onclick="nextQuestion()" class="btn" style="margin-top: 10px;">Следующий вопрос</button>
    </div>
    <div id="score" style="margin-top: 20px; padding: 10px; background: #f0f0f0; border-radius: 8px;">
        <span>Вопрос: <span id="question-number">0</span>/10</span>
        <span style="margin-left: 20px;">Правильно: <span id="correct-count">0</span></span>
    </div>
</div>

<script>
    const type = '<?= $type ?>';
    const region = '<?= $region ?? '' ?>';
    const questionsPerGame = 10;
    let currentQuestion = null;
    let answeredQuestions = [];
    let correctCount = 0;
    let questionNumber = 0;
    let isAnswering = false;

    function loadQuestion() {
        const exclude = answeredQuestions.join(',');
        let url = `/api/question?type=${type}&exclude=${exclude}`;
        if (region) {
            url += `&region=${encodeURIComponent(region)}`;
        }

        fetch(url)
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

        let html = `<div class="question-type-badge">${data.type_label}</div>`;
        html += `<div class="question-text" style="font-size: 20px; margin-bottom: 20px;">${questionData.question_text}</div>`;

        if (data.type === 'flag_to_country') {
            const flagUrl = questionData.flag_url || '';
            html += `
            <div style="text-align: center; margin: 20px 0;">
                <img src="${flagUrl}" alt="Флаг" style="max-width: 200px; height: auto; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            </div>
        `;
        }

        if (data.type === 'country_to_flag') {
            html += '<div class="flags-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 20px 0;">';
            const options = [...data.options];
            shuffleArray(options);
            options.forEach((option) => {
                const flagUrl = option.flag_url || '';
                const countryName = option.name || 'Unknown';
                const safeCountryName = countryName.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                html += `
                <div class="flag-option" style="text-align: center; padding: 15px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; transition: all 0.3s; background: white;" onclick="submitAnswer('${safeCountryName}')" data-country="${safeCountryName}">
                    <img src="${flagUrl}" alt="Флаг" style="max-width: 100px; height: auto; margin: 0 auto; display: block;">
                </div>
            `;
            });
            html += '</div>';
        }

        if (data.options && data.options.length > 0 && data.type !== 'country_to_flag') {
            html += '<div class="options" style="margin-top: 20px;">';
            const options = [...data.options];
            shuffleArray(options);
            options.forEach((option) => {
                const label = option.name || option.capital || 'Option';
                const safeLabel = label.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                html += `<button class="option-btn" onclick="submitAnswer('${safeLabel}')" style="display: block; width: 100%; padding: 12px; margin: 8px 0; border: 2px solid #ddd; border-radius: 4px; background: white; cursor: pointer; font-size: 16px; transition: all 0.3s;">${label}</button>`;
            });
            html += '</div>';
        }

        container.innerHTML = html;

        document.getElementById('question-number').textContent = questionNumber + 1;
        document.getElementById('result').style.display = 'none';
        isAnswering = false;
    }

    function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }

    function submitAnswer(answer) {
        if (isAnswering) return;
        isAnswering = true;

        document.querySelectorAll('.option-btn, .flag-option').forEach(el => {
            el.style.pointerEvents = 'none';
            el.style.opacity = '0.5';
            el.style.cursor = 'default';
        });

        const formData = new FormData();
        formData.append('question_id', currentQuestion.id);
        formData.append('answer', answer);

        fetch('/api/answer', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById('result');
                const messageDiv = resultDiv.querySelector('.result-message');

                if (currentQuestion.type === 'flag_to_country') {
                    document.querySelectorAll('.option-btn').forEach(btn => {
                        if (btn.textContent === data.correct_answer) {
                            btn.style.backgroundColor = '#90EE90';
                            btn.style.borderColor = '#228B22';
                        }
                        if (!data.is_correct && btn.textContent === answer) {
                            btn.style.backgroundColor = '#FFB6C1';
                            btn.style.borderColor = '#DC143C';
                        }
                    });
                } else if (currentQuestion.type === 'country_to_flag') {
                    document.querySelectorAll('.flag-option').forEach(el => {
                        const countryName = el.getAttribute('data-country');
                        if (countryName === data.correct_answer) {
                            el.style.borderColor = '#228B22';
                            el.style.backgroundColor = '#90EE90';
                            el.style.boxShadow = '0 0 10px rgba(34, 139, 34, 0.3)';
                        }
                        if (!data.is_correct && countryName === answer) {
                            el.style.borderColor = '#DC143C';
                            el.style.backgroundColor = '#FFB6C1';
                            el.style.boxShadow = '0 0 10px rgba(220, 20, 60, 0.3)';
                        }
                    });
                } else {
                    document.querySelectorAll('.option-btn').forEach(btn => {
                        if (btn.textContent === data.correct_answer) {
                            btn.style.backgroundColor = '#90EE90';
                            btn.style.borderColor = '#228B22';
                        }
                        if (!data.is_correct && btn.textContent === answer) {
                            btn.style.backgroundColor = '#FFB6C1';
                            btn.style.borderColor = '#DC143C';
                        }
                    });
                }

                if (data.is_correct) {
                    correctCount++;
                    messageDiv.textContent = '✅ Правильно!';
                    messageDiv.style.color = 'green';
                } else {
                    const correctAnswer = data.correct_answer || 'неизвестно';
                    messageDiv.textContent = `❌ Неправильно. Правильный ответ: ${correctAnswer}`;
                    messageDiv.style.color = 'red';
                }

                document.getElementById('correct-count').textContent = correctCount;

                resultDiv.style.display = 'block';
                resultDiv.style.background = '#f8f9fa';
                resultDiv.style.padding = '15px';
                resultDiv.style.borderRadius = '8px';
                resultDiv.style.marginTop = '20px';

                answeredQuestions.push(currentQuestion.id);
                questionNumber++;
                isAnswering = false;
            })
            .catch(error => {
                console.error('Error submitting answer:', error);
                const resultDiv = document.getElementById('result');
                const messageDiv = resultDiv.querySelector('.result-message');
                messageDiv.textContent = '❌ Ошибка при проверке ответа. Попробуйте обновить страницу.';
                messageDiv.style.color = 'red';
                resultDiv.style.display = 'block';
                isAnswering = false;
            });
    }

    function nextQuestion() {
        if (questionNumber >= questionsPerGame) {
            finishGame();
        } else {
            document.getElementById('result').style.display = 'none';
            document.querySelectorAll('.option-btn, .flag-option').forEach(el => {
                el.style.pointerEvents = 'auto';
                el.style.opacity = '1';
                el.style.cursor = 'pointer';
                el.style.backgroundColor = '';
                el.style.borderColor = '';
                el.style.boxShadow = '';
            });
            loadQuestion();
        }
    }

    function finishGame() {
        const container = document.getElementById('quiz-container');
        const percentage = questionNumber > 0 ? Math.round((correctCount / questionNumber) * 100) : 0;

        let replayUrl = '/quiz/play?type=' + encodeURIComponent(type);
        if (region) {
            replayUrl += '&region=' + encodeURIComponent(region);
        }

        container.innerHTML = `
            <h2>🎉 Игра завершена!</h2>
            <p>Правильных ответов: ${correctCount} из ${questionNumber}</p>
            <p>Процент правильных ответов: ${percentage}%</p>
            <div style="margin-top: 20px;">
                <a href="/" class="btn" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;">🏠 На главную</a>
                <a href="${replayUrl}" class="btn btn-secondary" style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">🔄 Играть ещё раз</a>
            </div>
        `;
    }

    loadQuestion();
</script>

<style>
.question-type-badge {
    display: inline-block;
    background: #e8ecf1;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    color: #666;
    margin-bottom: 10px;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';