package com.cia.mido;

import android.content.Intent;
import android.content.SharedPreferences;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.telecom.Call;
import android.util.Log;

import com.facebook.CallbackManager;
import com.facebook.FacebookCallback;
import com.facebook.FacebookException;
import com.facebook.FacebookSdk;
import com.facebook.appevents.AppEventsLogger;
import com.facebook.login.LoginResult;
import com.facebook.login.widget.LoginButton;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.Arrays;
import java.util.List;

public class FacebookLoginActivity extends AppCompatActivity {
    private CallbackManager callbackManager;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        FacebookSdk.sdkInitialize(getApplicationContext());
        callbackManager = CallbackManager.Factory.create();

        setContentView(R.layout.activity_facebook_login);

        customizeFacebookLoginButton();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        callbackManager.onActivityResult(requestCode, resultCode, data);
    }

    @Override
    public void onBackPressed() {
        Intent socialMediaIntent = new Intent(this, SocialMediaActivity.class);
        startActivity(socialMediaIntent);
        finish();
    }

    private void customizeFacebookLoginButton() {
        LoginButton facebookLoginButton = (LoginButton) findViewById(R.id.facebookLoginButton);
        facebookLoginButton.setReadPermissions("email");
        facebookLoginButton.clearPermissions();
        facebookLoginButton.setPublishPermissions(Arrays.asList("manage_pages", "publish_pages"));

        facebookLoginButton.registerCallback(callbackManager, new FacebookCallback<LoginResult>() {
            @Override
            public void onSuccess(LoginResult loginResult) {
                String url = getResources().getString(R.string.backEndUrl) + getResources().getString(R.string.facebookLoginUrl);
                JSONObject request = new JSONObject();

                try {
                    request.put("access_token", loginResult.getAccessToken().getToken());
                    Client client = new Client(url, request, "POST");
                    client.execute();

                    JSONObject response;
                    do {
                        Thread.sleep(1000);
                        response = client.getResponse();
                    } while (response == null);

                    SharedPreferences sharedPreferences = getSharedPreferences("com.cia.mido", MODE_PRIVATE);
                    SharedPreferences.Editor editor = sharedPreferences.edit();
                    editor.putString("userId", response.getString("user_id"));
                    editor.commit();
                } catch (Exception e) {
                    e.printStackTrace();
                }

                Intent intent = new Intent(FacebookLoginActivity.this, FacebookPagesActivity.class);
                startActivity(intent);
                finish();
            }

            @Override
            public void onCancel() {

            }

            @Override
            public void onError(FacebookException error) {

            }
        });
    }
}