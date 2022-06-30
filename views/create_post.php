<?php
include "views/inc/head.php";
?>

<div class="container">
    <div class="row mt-5">
        <div class="col-md-6 offset-md-3 my-4 py-3">
            <div class="card bg-light p-4">
                <h3><i class="fa fa-pencil" aria-hidden="true"></i> Create a Post</h3>
                <form action="<?= ROOT ?>posts/create" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                      <label for="title">Post Title</label>
                      <input type="text" name="title" class="form-control" placeholder="Post title....">
                    </div>
                    <div class="form-group">
                      <label for="body">Post Body</label>
                      <div id="editor"></div>
                    </div>
                    <div class="form-group">
                      <label for="title">Post Image</label>
                      <input type="file" name="image" class="form-control">
                    </div>
                    <input type="text" name="body" class="form-control body">
                    <?php CSRF::outputToken(); ?>
                    <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="fa fa-plus-square" aria-hidden="true"></i> Create New Post</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Include the Quill library -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<!-- Initialize Quill editor -->
<script>
  var quill = new Quill('#editor', {
    theme: 'snow'
  });
  let editor = document.querySelector(".ql-editor");
  let body = document.querySelector("input.body");
  editor.addEventListener("keyup", function() {
    body.value = editor.innerHTML;
  })
</script>
<?php
include "views/inc/footer.php";
?>