<?php


namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RecipeController
{
    private $recipeRepository;

    public function __construct(RecipeRepository $recipeRepository)
    {
        $this->recipeRepository = $recipeRepository;
    }





    /**
     * gets all recipes
     * @Route("/recipes", name="recipe_list", methods={"GET"})
     */
    public function getRecipes(Request $request) : JsonResponse
    {
        $recipes = $this->recipeRepository->findAll();
        $data = [];
        foreach ($recipes as $recipe)
        {
            array_push($data,$recipe->toArray());
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }



    /**
     * gets a single recipe entry
     * @Route("/recipes/{id}", name="recipe_one", methods={"GET"})
     */
    public function getRecipe(Request $request,$id) : JsonResponse
    {
        $recipe = $this->recipeRepository->findById($id);

        return new JsonResponse($recipe->toArray(), Response::HTTP_OK);
    }



    /**
     * adds a recipe to the database
     * one recipe has a minimum of one ingredient
     * recipe ingredients are created only if they don't already exist in the database
     * @Route("/recipes", name="recipe_post", methods={"POST"})
     */

    public function addRecipe(Request $request) : JsonResponse
    {
        $data = json_decode($request->getContent(),true);

        //verifying if user input is correct

        if (empty($data['title']))
        {
            return new JsonResponse(["msg" => "error : title missing"], Response::HTTP_BAD_REQUEST);
        }

        $title = $data['title'];

        if (empty($data['ingredients'])or count($data['ingredients'])==0)
        {
            return new JsonResponse(["msg" => "error : a minimum of one ingredient is required"], Response::HTTP_BAD_REQUEST);
        }

        $ingredients = $data['ingredients'];
        foreach ($ingredients as $ingredient)
        {
            if (empty($ingredient["name"]))
            {
                return new JsonResponse(["msg" => "error : ingredient name missing"], Response::HTTP_BAD_REQUEST);
            }
        }


        if (!empty($data['sub_title']))
        {
            $sub_title = $data['sub_title'];
        }
        else
        {
            $sub_title = null;
        }


        //saving the new recipe in the database using the repository object

        $recipe = $this->recipeRepository->addRecipe($title,$sub_title,$ingredients);

        $message = ["msg" => "recipe created", "recipe"=>$recipe->toArray() ];

        return new JsonResponse($message, Response::HTTP_OK);
    }

    /**
     * @Route("/recipes/{id}", name="recipe_update", methods={"PUT"})
     * updates an existing recipe, the ingredients list will be overwritten by the new ingredients array
     */
    public function updateRecipe(Request $request,$id) : JsonResponse
    {
        $data = json_decode($request->getContent(),true);


        $recipe = $this->recipeRepository->findById($id);

        $title = null;
        $sub_title = null;
        $ingredients = null;

        if ($recipe==null)
        {
            return new JsonResponse(["msg" => "error : id incorrect"],Response::HTTP_BAD_REQUEST);
        }

        /**
         * @var $recipe Recipe
         */
        if (!empty($data['title']))
        {
            $title = $data['title'];
        }
        if (!empty($data['sub_title']))
        {
            $sub_title = $data['sub_title'];
        }

        if (!empty($data['ingredients']))
        {
            $ingredients = $data['ingredients'];
        }

        $this->recipeRepository->updateRecipe($id,$title,$sub_title,$ingredients);

        $message = ["msg" => "recipe updated", "recipe"=>$recipe->toArray() ];

        return new JsonResponse($message, Response::HTTP_OK);

    }

    /**
     * @Route("/recipes/{id}", name="recipe_delete", methods={"DELETE"})
     * deletes recipe
     */
    public function deleteRecipe($id) : JsonResponse
    {
        //$data = $request->get("id");


        $recipe = $this->recipeRepository->findById($id);

        if ($recipe==null)
        {
            return new JsonResponse(["msg" => "error : id incorrect"],Response::HTTP_BAD_REQUEST);
        }

        /**
         * @var $recipe Recipe
         */

        $this->recipeRepository->deleteRecipe($recipe);

        return new JsonResponse(["msg" => "recipe deleted"],Response::HTTP_OK);
    }

}