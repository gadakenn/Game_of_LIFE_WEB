const newsSingleAll = document.querySelectorAll(".news-container .news-single");

let currentActive = 0;
let totalNews = newsSingleAll.length;
let duration = 3000;

const removeAllActive = () => {
  newsSingleAll.forEach((n) => {
    n.classList.remove("active");
  });
};

const changeNews = () => {
  if (currentActive >= totalNews - 1) {
    currentActive = 0;
  } else {
    currentActive += 1;
  }

  removeAllActive();
  newsSingleAll[currentActive].classList.add("active");
};

setInterval(changeNews, duration);


document.addEventListener('DOMContentLoaded', function() {
  window.showAnalysis = function(element) {
      var analysisText = element.getAttribute('data-analysis');
      var analysisContainer = document.getElementById('analysis-container');
      analysisContainer.innerHTML = analysisText;
      analysisContainer.style.display = 'block';
  };

  document.addEventListener('click', function(event) {
      var analysisContainer = document.getElementById('analysis-container');
      var newsLinks = document.querySelectorAll('.news-single');
      if (!analysisContainer.contains(event.target) && !Array.from(newsLinks).some(link => link.contains(event.target))) {
          analysisContainer.style.display = 'none';
      }
  });
});

