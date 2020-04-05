$(document).ready(function () {

    let url = "http://newsapi.org/v2/everything?sources=bbc-news&sortBy=latest&apiKey=daebf297e7cc4c899900e62700ebded1";
  
    $.ajax({
      url: url,
      method: "GET",
      dataType: "JSON",

      beforeSend: function () {
        $(".progress").show();
      },
  
      complete: function () {
        $(".progress").hide();
      },  
  
      success: function (newsdata) {
        let output = "";
        let latestNews = newsdata.articles;
        for (var i in latestNews) {
          output += `
                <div class="s_Posts">
                    <img src="${latestNews[i].urlToImage}" class="responsive-img" alt="${latestNews[i].title}" height="100%" width="100%">
                    <h3>Title: <a href="${latestNews[i].url}" target="blank" title="${latestNews[i].title}">${latestNews[i].title}</a></h3>
                    <p><strong>News source</strong>: ${latestNews[i].source.name} </p>
                    <p><strong>Description</strong>: ${latestNews[i].description}</p>
                    <p><strong>Published</strong>: ${latestNews[i].publishedAt} </p>
                    <a href="${latestNews[i].url}" target="blank" class="btn">Read More</a>
                </div>
          `;
        }
  
        if (output !== "") {
          $("#newsResults").html(output);
        }
  
      },
  
      error: function () {
        let errorMsg = `<div class="errorMsg center">An error occured, couldn't connect to the news server.</div>`;
        $("#newsResults").html(errorMsg);
      }
    })
  
  });