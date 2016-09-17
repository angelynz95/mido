package com.cia.mido;

import android.content.Intent;
import android.content.SharedPreferences;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.widget.ArrayAdapter;
import android.widget.ListView;

import com.facebook.AccessToken;
import com.facebook.AccessTokenTracker;
import com.facebook.CallbackManager;
import com.facebook.FacebookSdk;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

public class FacebookPagesActivity extends AppCompatActivity {
    private AccessToken accessToken;
    private AccessTokenTracker accessTokenTracker;
    private CallbackManager callbackManager;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        FacebookSdk.sdkInitialize(getApplicationContext());
        callbackManager = CallbackManager.Factory.create();

        setContentView(R.layout.activity_facebook_pages);

        getAccessToken();
        showFacebookPages();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        callbackManager.onActivityResult(requestCode, resultCode, data);
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        accessTokenTracker.stopTracking();
    }

    @Override
    public void onBackPressed() {
        Intent socialMediaIntent = new Intent(this, SocialMediaActivity.class);
        startActivity(socialMediaIntent);
        finish();
    }

    private void getAccessToken() {
        accessTokenTracker = new AccessTokenTracker() {
            @Override
            protected void onCurrentAccessTokenChanged(AccessToken oldAccessToken, AccessToken currentAccessToken) {
                accessToken = currentAccessToken;
                if (accessToken == null) {
                    Intent facebookLoginIntent = new Intent(FacebookPagesActivity.this, FacebookLoginActivity.class);
                    startActivity(facebookLoginIntent);
                    finish();
                }
            }
        };
        accessToken = AccessToken.getCurrentAccessToken();
    }

    private void showFacebookPages() {
        SharedPreferences sharedPreferences = getSharedPreferences(getResources().getString(R.string.packageName), MODE_PRIVATE);
        String userId = sharedPreferences.getString("userId", null);
        String url = (getResources().getString(R.string.backEndUrl) + getResources().getString(R.string.facebookPagesUrl)).replace("{userId}", userId);
        Client client = new Client(url, null, "GET");
        client.execute();

        JSONObject response;
        List<String> facebookPageNames = new ArrayList<>();
        try {
            do {
                Thread.sleep(1000);
                response = client.getResponse();
            } while (response == null);

            SharedPreferences.Editor editor = sharedPreferences.edit();
            JSONArray facebookPages = (JSONArray) response.get("pages");
            for (int i = 0; i < facebookPages.length(); i++) {
                JSONObject facebookPage = (JSONObject) facebookPages.get(i);
                String facebookPageId = facebookPage.getString("id");
                String facebookPageName = facebookPage.getString("name");
                facebookPageNames.add(facebookPageName);
                editor.putString(facebookPageId + "-" + facebookPageName, facebookPageId);
            }
            editor.commit();
        } catch (Exception e) {
            e.printStackTrace();
        }

        ArrayAdapter adapter = new ArrayAdapter<String>(this, R.layout.facebook_page_listview, facebookPageNames);
        ListView facebookPageList = (ListView) findViewById(R.id.facebookPageList);
        facebookPageList.setAdapter(adapter);
    }
}
