console.log("welcome to ITEC MVC");
let apiBtn = document.querySelector("a.api");
apiBtn.addEventListener("click", function(event) {
    event.preventDefault();
    fetchPostsApi();
});

function fetchPostsApi() {
    let xhr = new XMLHttpRequest;
    xhr.open("get", "api/posts?offset=0&limit=2", true);
    xhr.onload = function() {
        let res = JSON.parse(this.responseText);
        console.log(res);
    }

    xhr.send();
}