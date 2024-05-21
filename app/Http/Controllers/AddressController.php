<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressCreateRequest;
use App\Http\Requests\AddressUpdateRequest;
use App\Http\Resources\AddressResource;
use App\Http\Resources\ContactResource;
use App\Models\Address;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    private function getContact(User $user, int $idContact): Contact
    {
        $contacts = Contact::where("user_id", $user->id)->where("id", $idContact)->first();

        if (!$contacts) {
            throw new HttpResponseException(response()->json([
                "errors" => [
                    "message" => [
                        "not found"
                    ]
                ]
            ], 404));
        }
        return $contacts;
    }

    private function getAddress(Contact $contacts, int $idAddress): Address
    {
        $address = Address::query()->where("id", $idAddress)->where("contact_id", $contacts->id)->first();

        if (!$address) {
            throw new HttpResponseException(response()->json([
                "errors" => [
                    "message" => [
                        "not found"
                    ]
                ]
            ], 404));
        }
        return $address;
    }

    public function create(int $idContact, AddressCreateRequest $request): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $idContact);

        $data = $request->validated();
        $address = new Address($data);
        $address->contact_id = $idContact;
        $address->save();

        return (new AddressResource($address))->response()->setStatusCode(201);
    }

    public function get(int $idContact, int $idAddress): AddressResource
    {
        $user = Auth::user();
        $contacts = $this->getContact($user, $idContact);

        $address = $this->getAddress($contacts, $idAddress);

        return new AddressResource($address);
    }

    public function update(int $idContact, int $idAddress, AddressUpdateRequest $request): AddressResource
    {
        $user = Auth::user();
        $contacts = $this->getContact($user, $idContact);
        $address = $this->getAddress($contacts, $idAddress);

        $data = $request->validated();
        $address->fill($data);
        $address->save();

        return new AddressResource($address);
    }

    public function delete(int $idContact, int $idAddress): JsonResponse
    {
        $user = Auth::user();
        $contacts = $this->getContact($user, $idContact);
        $address = $this->getAddress($contacts, $idAddress);

        $address->delete();

        return response()->json([
            "data" => true
        ])->setStatusCode(200);
    }

    public function list(int $idContact): JsonResponse
    {
        $user = Auth::user();
        $contacts = $this->getContact($user, $idContact);
        $addresses = Address::where("contact_id", $contacts->id)->get();

        return AddressResource::collection($addresses)->response()->setStatusCode(200);
    }
}
